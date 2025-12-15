<?php
session_start();
require_once '../../Controllers/blogC.php';
require_once '../../Controllers/commentaireC.php';
require_once '../../Controllers/likeC.php';

$blogC = new blogC();
$liste = $blogC->publier();
$commentaireC = new commentaireC();
$likeC = new likeC();

// ==== FAVORIS + FILTRE (VERSION FINALE ULTIME – TOUT MARCHE) ====
if (!isset($_SESSION['favoris'])) {
    $_SESSION['favoris'] = [];
}
    
// Toggle favori
if (isset($_GET['fav'])) {
    $id = (int)$_GET['fav'];
    $filter = $_GET['filter'] ?? 'toutes';

    if (in_array($id, $_SESSION['favoris'])) {
        $_SESSION['favoris'] = array_values(array_diff($_SESSION['favoris'], [$id]));
    } else {
        $_SESSION['favoris'][] = $id;
    }
    header("Location: ?filter=$filter");
    exit;
}

// === RECHERCHE PAR UTILISATEUR + FILTRAGE ===
$filter = $_GET['filter'] ?? 'toutes';
$search_user = trim($_GET['search_user'] ?? '');

if (!empty($search_user)) {
    $sql = "SELECT id, prenom, nom FROM utilisateur 
            WHERE CONCAT(prenom, ' ', nom) LIKE :search 
               OR prenom LIKE :search 
               OR nom LIKE :search
            ORDER BY 
                CASE WHEN CONCAT(prenom, ' ', nom) = :exact THEN 0 ELSE 1 END, 
                prenom, nom
            LIMIT 20";
    
    $db = config::getConnexion();
    $req = $db->prepare($sql);
    $req->execute([
        ':search' => '%' . $search_user . '%',
        ':exact'  => $search_user
    ]);
    $users = $req->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($users)) {
        $user_id = $users[0]['id'];
        $user_name = trim($users[0]['prenom'] . ' ' . $users[0]['nom']);

        $sql = "SELECT b.*, u.prenom, u.nom 
                FROM blog b 
                LEFT JOIN utilisateur u ON b.utilisateur_id = u.id 
                WHERE b.utilisateur_id = :user_id 
                  AND b.statut = 'approuve'
                ORDER BY b.createdAt DESC";
        $req = $db->prepare($sql);
        $req->execute([':user_id' => $user_id]);
        $liste = $req->fetchAll(PDO::FETCH_ASSOC);

        $titre = "Publications de " . htmlspecialchars($user_name);
    } else {
        $liste = [];
        $titre = "Aucun utilisateur trouvé pour « " . htmlspecialchars($search_user) . " »";
    }
}
elseif ($filter === 'mes' && isset($_SESSION['user_id'])) {
    $sql = "SELECT b.*, u.prenom, u.nom 
            FROM blog b 
            LEFT JOIN utilisateur u ON b.utilisateur_id = u.id 
            WHERE b.utilisateur_id = :user_id 
              AND b.statut = 'approuve'
            ORDER BY b.createdAt DESC";
    $db = config::getConnexion();
    $req = $db->prepare($sql);
    $req->execute([':user_id' => $_SESSION['user_id']]);
    $liste = $req->fetchAll(PDO::FETCH_ASSOC);
    $titre = "Mes publications";
}
elseif ($filter === 'enregistrees') {
    $liste = [];
    if (!empty($_SESSION['favoris'])) {
        foreach ($_SESSION['favoris'] as $id) {
            $post = $blogC->getPostById($id);
            if ($post) {
                $sql = "SELECT prenom, nom FROM utilisateur WHERE id = :uid";
                $db = config::getConnexion();
                $req = $db->prepare($sql);
                $req->execute([':uid' => $post['utilisateur_id']]);
                $user = $req->fetch(PDO::FETCH_ASSOC);
                $post['prenom'] = $user['prenom'] ?? 'Utilisateur';
                $post['nom'] = $user['nom'] ?? '';
                $liste[] = $post;
            }
        }
    }
    $titre = "Publications enregistrées";
}
else {
    $liste = $blogC->publier();
    $titre = "Fil de discussion";
    $filter = 'toutes';
}

// AVATAR BLEU FIXE IDENTIQUE POUR TOUT LE MONDE
$avatarSVG = '<svg width="100%" height="100%" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
  <circle cx="50" cy="50" r="50" fill="#374151"/>
  <circle cx="50" cy="35" r="18" fill="#9ca3af"/>
</svg>';

// === Liste des utilisateurs pour l'autocomplete (sans AJAX) ===
$sql = "SELECT DISTINCT prenom, nom FROM utilisateur ORDER BY prenom, nom";
$db = config::getConnexion();
$req = $db->prepare($sql);
$req->execute();
$all_users = $req->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>medsense</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #fff 0%, #f0f9ff 50%, #e0f2fe 100%);
            background-attachment: fixed;
            color: #1e293b;
            min-height: 100vh;
            padding: 20px 0;
            position: relative;
        }
        body::before {
            content: ''; position: fixed; inset: 0; pointer-events: none; z-index: 0;
            background: radial-gradient(circle at 20% 50%, rgba(147,197,253,.08), transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(103,232,249,.06), transparent 50%);
        }

        .header {
            background: rgba(255,255,255,.95);
            backdrop-filter: blur(20px);
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 20px rgba(0,0,0,.04);
            border-bottom: 1px solid rgba(147,197,253,.2);
            margin: -20px -20px 30px;
        }
        .logo img { width: 200px; }
        
        /* AVATAR BLEU FIXE */
        .avatar, .user-avatar {
            width: 44px; height: 44px; border-radius: 50%; 
            border: 2.5px solid #3b82f6; box-shadow: 0 2px 8px rgba(59,130,246,.2);
            overflow: hidden; background: #2563eb; flex-shrink: 0;
        }
        .comment .avatar { width: 34px; height: 34px; }

        .chat { max-width: 700px; margin: 0 auto; padding: 0 20px; }
        h1 { text-align: center; color: #1e40af; margin-bottom: 20px; font-size: 24px; font-weight: 600; }

        ul { list-style: none; display: flex; flex-direction: column; gap: 16px; }
        li {
            background: white;
            border-radius: 18px;
            box-shadow: 0 4px 16px rgba(0,0,0,.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        /* POST HEADER */
        .post-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 16px; position: relative;
        }
        .user-info { display: flex; align-items: center; gap: 12px; }
        .username { font-weight: 600; color: #1e40af; font-size: 15px; }
        .post-time { color: #64748b; font-size: 12px; margin-left: auto; margin-right: 8px; }

        /* MENU 3 POINTS */
        .menu-toggle {
            width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: #94a3b8; font-size: 14px; transition: .2s;
        }
        .menu-toggle:hover { background: #e2e8f0; color: #1e40af; }
        .menu-dropdown {
            position: absolute; right: 8px; top: 38px;
            background: white; border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,.15);
            border: 1px solid #e2e8f0; overflow: hidden;
            opacity: 0; visibility: hidden; transform: translateY(-8px);
            transition: .2s; z-index: 10; min-width: 140px;
        }
        .menu-dropdown.show { opacity: 1; visibility: visible; transform: none; }
        .menu-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; font-size: 13.5px; color: #334155;
            text-decoration: none; transition: .2s;
        }
        .menu-item:hover { background: #f1f5f9; }
        .menu-item.delete { color: #dc2626; }

        .post-content { padding: 0 16px 12px; font-size: 15px; line-height: 1.5; color: #334155; word-break: break-word; }
        .post-image { width: 100%; max-height: 400px; object-fit: cover; display: block; }

        /* BOUTONS EN HAUT */
        .interactions {
            display: flex; align-items: center; gap: 24px;
            padding: 10px 16px; background: white;
            border-top: 1px solid #e2e8f0; font-size: 13.5px; color: #64748b;
        }
        .action-btn {
            display: flex; align-items: center; gap: 6px; font-weight: 500;
            cursor: pointer; transition: .2s;
        }
        .action-btn i { font-size: 16px; color: #94a3b8; }
        .action-btn:hover, .action-btn.active { color: #3b82f6; }
        .action-btn:hover i, .action-btn.active i { color: #3b82f6; }
        .comment-count { font-weight: 600; color: #64748b; }
        .action-btn.active .comment-count { color: #3b82f6; }

        /* SECTION COMMENTAIRES */
        .comments-section {
            max-height: 0; overflow: hidden;
            transition: max-height .4s ease; background: #f8fafc;
        }
        .comments-section.open { max-height: 2000px; }

        .comments-list { padding: 0 16px 8px; }
        .comment {
            display: flex; gap: 12px; margin-bottom: 12px; padding-top: 8px;
            position: relative;
        }
        .comment-body {
            flex: 1; background: white; padding: 10px 14px;
            border-radius: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .comment-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 4px;
        }
        .comment-info { display: flex; align-items: center; gap: 8px; }
        .comment-body strong { color: #1e40af; font-size: 14px; }
        .comment-body .time { font-size: 11px; color: #94a3b8; }
        .comment-text { font-size: 14px; color: #334155; word-break: break-word; margin: 4px 0 0; }

        /* FORMULAIRE AJOUT COMMENTAIRE */
        .comment-form {
            display: flex; gap: 10px; padding: 12px 16px 16px;
            align-items: center; background: #f8fafc;
        }
        .comment-form input[type="text"] {
            flex: 1; padding: 11px 16px; border: 1.5px solid #e2e8f0;
            border-radius: 24px; font-size: 14.5px; outline: none;
            background: white; transition: .2s;
        }
        .comment-form input:focus {
            border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
        }
        .comment-form input:disabled {
            background: #f1f5f9 !important;
            color: #94a3b8;
            cursor: not-allowed;
            opacity: 0.8;
        }
        .comment-form button {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white; border: none; width: 42px; height: 42px;
            border-radius: 50%; cursor: pointer; font-size: 17px;
            box-shadow: 0 4px 12px rgba(59,130,246,0.3);
            transition: .2s; display: flex; align-items: center; justify-content: center;
        }
        .comment-form button:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(59,130,246,0.4); }

        /* BOUTON AJOUTER */
        .add-post-btn {
            position: fixed;
            bottom: 28px;
            right: 28px;
            width: 62px;
            height: 62px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
            z-index: 999;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 4px solid white;
            backdrop-filter: blur(10px);
        }
        .add-post-btn:hover {
            transform: translateY(-6px) scale(1.08);
            box-shadow: 0 16px 40px rgba(59, 130, 246, 0.5);
            background: linear-gradient(135deg, #2563eb, #1e40af);
        }

        /* CŒUR ROUGE QUAND LIKÉ */
        .action-btn.liked { color: #ef4444 !important; }
        .action-btn.liked i { color: #ef4444 !important; animation: beat 0.6s ease-in-out; }
        @keyframes beat { 0%,100% { transform: scale(1); } 50% { transform: scale(1.4); } }
        .action-btn.liked .like-count { color: #dc2626; font-weight: 700; }
        
        /* Sidebar gauche fixe comme Grok */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 280px;
            background: rgba(255,255,255,0.94);
            backdrop-filter: blur(16px);
            border-right: 1px solid rgba(0,0,0,0.08);
            padding: 100px 20px 40px;
            z-index: 50;
            overflow-y: auto;
            pointer-events: auto;
        }
        .sidebar h3 { font-size: 11px; text-transform: uppercase; color: #64748b; margin: 30px 0 12px; letter-spacing: 1px; font-weight: 600; }
        .sidebar a {
            display: flex; align-items: center; gap: 14px; padding: 11px 16px;
            border-radius: 12px; color: #334155; text-decoration: none; font-weight: 500;
            transition: 0.2s;
        }
        .sidebar a:hover { background: rgba(59,130,246,0.12); }
        .sidebar a.active { background: #3b82f6; color: white; font-weight: 600; }
        .sidebar a i { font-size: 18px; width: 24px; text-align: center; }
        .main-content {
            margin-left: 280px;
            padding-top: 20px;
        }

        @media (max-width: 992px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
        }

        /* STYLE SWEETALERT2 */
        .swal2-popup {
            border-radius: 18px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
        }
        .swal2-title {
            color: #1e40af !important;
            font-weight: 600 !important;
            font-size: 20px !important;
        }
        .swal2-html-container {
            color: #334155 !important;
            font-size: 15px !important;
        }
        .swal2-confirm.swal2-styled {
            background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
            color: white !important;
            padding: 13px 30px !important;
            border-radius: 12px !important;
            font-weight: 600 !important;
            font-size: 16px !important;
            box-shadow: 0 4px 15px rgba(59,130,246,0.4) !important;
        }
        .swal2-confirm.swal2-styled:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 20px rgba(59,130,246,0.5) !important;
        }
        .swal2-cancel.swal2-styled {
            background: #e2e8f0 !important;
            color: #64748b !important;
            padding: 13px 26px !important;
            border-radius: 12px !important;
            font-weight: 600 !important;
            font-size: 16px !important;
            margin-left: 12px !important;
        }
        .username:hover { text-decoration: underline; opacity: 0.9; }
     .search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    max-height: 380px;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    z-index: 1000;
    margin-top: 8px;
    display: none;
}

.suggestion-item {
    padding: 14px 20px;
    cursor: pointer;
    transition: background 0.2s;
    border-bottom: 1px solid #f1f5f9;
    font-size: 15px;
    color: #334155;
}

.suggestion-item:hover {
    background: #f0f9ff;
    color: #1e40af;
    font-weight: 600;
}

.suggestion-item:last-child {
    border-bottom: none;
}
.translate-btn i {
    font-size: 15px;
    opacity: 0.9;
}

.translate-btn:hover {
    background: #f0e8ff !important;
    color: #8b5cf6;
}

.translate-btn:hover i {
    animation: spin 1.5s linear infinite;
}
    </style>
</head>
<body>
    <script>
        const estConnecte = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
    </script>

    <!-- SIDEBAR + HEADER (inchangés) -->
    <div class="sidebar">
        <h3>Publications</h3>
        <a href="?filter=toutes" class="<?= $filter==='toutes'?'active':'' ?>">
            <i class="fas fa-home"></i> Toutes les publications
        </a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="?filter=mes" class="<?= $filter==='mes'?'active':'' ?>"><i class="fas fa-user"></i> Mes propres publications</a>
        <?php endif; ?>
        <?php if(isset($_SESSION['user_id'])): ?>
    <!-- Utilisateur connecté → accès normal -->
    <a href="?filter=enregistrees" class="<?= $filter==='enregistrees'?'active':'' ?>">
        <i class="fas fa-bookmark"></i> Publications enregistrées 
        <?php if(!empty($_SESSION['favoris'])): ?>
            <small style="margin-left:auto;background:#3b82f6;color:#fff;padding:2px 8px;border-radius:8px;">
                <?= count($_SESSION['favoris']) ?>
            </small>
        <?php endif; ?>
    </a>
<?php else: ?>
    <!-- Utilisateur NON connecté → alerte SweetAlert -->
    <a href="#" style="cursor:pointer;" 
       onclick="Swal.fire({
           icon: 'warning',
           title: 'Connexion requise',
           text: 'Vous devez vous connecter pour voir vos publications enregistrées',
           showCancelButton: true,
           confirmButtonText: 'Se connecter',
           cancelButtonText: 'Annuler',
           reverseButtons: true
       }).then((result) => {
           if (result.isConfirmed) {
               window.location.href = 'login.php';
           }
       }); return false;">
        <i class="fas fa-bookmark"></i> Publications enregistrées
    </a>
<?php endif; ?>
    </div>

    <?php
        $nomUtilisateurConnecte = "Invité";
        if (isset($_SESSION['user_id']) && isset($_SESSION['prenom']) && isset($_SESSION['nom'])) {
            $nomUtilisateurConnecte = trim($_SESSION['prenom'] . " " . $_SESSION['nom']);
        } elseif (isset($_SESSION['email'])) {
            $nomUtilisateurConnecte = $_SESSION['email'];
        }
    ?>
    <header class="header">
    <div style="display: flex; align-items: center; gap: 20px; flex: 1;">
    <div class="logo">
        <img src="http://localhost/blog/logo.png" alt="logo">
    </div>

    <!-- Barre de recherche avec suggestions ... (tout le reste reste inchangé) -->
    <div style="position: relative; max-width: 500px; width: 100%; margin-left: 200px;">
        <!-- ton code de recherche reste exactement le même -->
    <form id="searchForm" method="GET" style="position: relative;">
        <input type="text" name="search_user" 
               id="searchInput" value="<?= isset($_GET['search_user']) ? htmlspecialchars($_GET['search_user']) : '' ?>" 
               placeholder="Rechercher un utilisateur..." 
               autocomplete="off"
               style="width: 100%; padding: 12px 50px 12px 20px; border: 2px solid #e2e8f0; border-radius: 30px; font-size: 15px; outline: none; transition: all .2s; background: white;"
               onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 4px rgba(59,130,246,0.15)';"
               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">

        <!-- Bouton loupe : on ajoute onclick pour forcer l'affichage des suggestions -->
        <button type="submit" id="searchButton" style="position: absolute; right: 6px; top: 4px; background: #3b82f6; color: white; border: none; width: 38px; height: 38px; border-radius: 50%; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-search"></i>
        </button>

        <?php if (isset($_GET['filter']) && $_GET['filter'] !== 'toutes'): ?>
            <input type="hidden" name="filter" value="<?= htmlspecialchars($_GET['filter']) ?>">
        <?php endif; ?>
    </form>

    <!-- Liste des suggestions (seulement le nom complet) -->
    <div id="searchSuggestions" class="search-suggestions">
        <?php foreach($all_users as $u): 
            $full_name = trim($u['prenom'] . ' ' . $u['nom']);
            if(empty($full_name)) continue;
        ?>
            <div class="suggestion-item" data-name="<?= htmlspecialchars($full_name) ?>">
                <?= htmlspecialchars($full_name) ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    const searchInput = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('searchSuggestions');
    const suggestionItems = document.querySelectorAll('.suggestion-item');
    const searchForm = document.getElementById('searchForm');
    const searchButton = document.getElementById('searchButton');

    // Fonction pour filtrer et afficher les suggestions
    function showSuggestions() {
        const query = searchInput.value.trim().toLowerCase();
        let hasVisible = false;

        suggestionItems.forEach(item => {
            const name = item.getAttribute('data-name').toLowerCase();
            if (query === '' || name.includes(query)) {
                item.style.display = 'block';
                hasVisible = true;
            } else {
                item.style.display = 'none';
            }
        });

        suggestionsBox.style.display = hasVisible ? 'block' : 'none';
    }

    // NOUVEAU : dès qu'on clique dans le champ → on ouvre la liste
    searchInput.addEventListener('focus', function() {
        if (searchInput.value.trim() === '') {
            suggestionItems.forEach(item => item.style.display = 'block');
            suggestionsBox.style.display = 'block';
        } else {
            showSuggestions();
        }
    });

    // Quand on tape → filtre en temps réel
    searchInput.addEventListener('input', showSuggestions);

    // Clic sur la loupe → ouvre aussi la liste (au cas où)
    searchButton.addEventListener('click', function(e) {
        e.preventDefault();
        showSuggestions();
        searchInput.focus();
    });

    // Clique sur une suggestion → remplit + lance la recherche
    suggestionItems.forEach(item => {
        item.addEventListener('click', function() {
            searchInput.value = this.getAttribute('data-name');
            suggestionsBox.style.display = 'none';
            searchForm.submit();
        });
    });

    // Masque la liste quand on clique ailleurs
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target) && e.target !== searchButton) {
            suggestionsBox.style.display = 'none';
        }
    });

    // Entrée → lance la recherche
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            suggestionsBox.style.display = 'none';
            searchForm.submit();
        }
    });

    // Quand on submit (loupe ou Entrée) → cache la liste
    searchForm.addEventListener('submit', function() {
        suggestionsBox.style.display = 'none';
    });
</script>
<!-- Icônes Accueil + Messages -->
<div style="position: absolute; right: 520px; top: 50%; transform: translateY(-50%); display: flex; gap: 25px; align-items: center; z-index: 1000; left: 990px;">
    
    <!-- Icône Accueil -->
    <a href="liste.php" 
       style="color: #64748b; font-size: 26px; transition: color 0.2s; text-decoration: none;" 
       onmouseover="this.style.color='#3b82f6'" 
       onmouseout="this.style.color='#64748b'" 
       title="Accueil">
        <i class="fas fa-house"></i>
    </a>

    <!-- Icône Messages (NOUVELLE) -->
    <a href="message.php" 
       style="color: #64748b; font-size: 26px; transition: color 0.2s; text-decoration: none;" 
       onmouseover="this.style.color='#3b82f6'" 
       onmouseout="this.style.color='#64748b'" 
       title="Messages">
        <i class="fas fa-envelope"></i>
    </a>
</div>
    </div>

    <!-- Partie droite : nom d'utilisateur + avatar + bouton -->
    <div style="display:flex; align-items:center; gap:20px;">
        <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['prenom']) && !empty($_SESSION['nom'])): ?>
            <div style="text-align:right; line-height:1.4;">
                <small style="color:#64748b; font-size:13px;">Connecté en tant que</small><br>
                <strong style="font-size:18px; color:#1e40af;">
                    <?= htmlspecialchars($_SESSION['prenom'] . " " . $_SESSION['nom']) ?>
                </strong>
            </div>
            <div class="avatar"><?= $avatarSVG ?></div>

            <a href="logout.php" style="background:#dc2626;color:white;padding:11px 22px;border-radius:12px;text-decoration:none;font-weight:600;box-shadow:0 4px 12px rgba(220,38,38,0.3);">
                Déconnexion
            </a>
        <?php else: ?>
            <a href="login.php" style="background:#3b82f6;color:white;padding:13px 30px;border-radius:12px;text-decoration:none;font-weight:600;font-size:16px;box-shadow:0 4px 15px rgba(59,130,246,0.3);">
                Se connecter
            </a>
        <?php endif; ?>
    </div>
</header>

    <div class="main-content">
    <div class="chat">
        <h1><?= $titre ?></h1>
        <ul>
            <?php foreach($liste as $p): 
                $nbComments = count($commentaireC->listeCommentaires($p['id']));
                $likesCount = $likeC->countLikes($p['id']);
                $isLiked = isset($_SESSION['user_id']) && $likeC->isLiked($_SESSION['user_id'], $p['id']);
            ?>
                <li id="post-<?= $p['id'] ?>">
                    <div class="post-header">
    <div class="user-info">
    <div class="user-avatar"><?= $avatarSVG ?></div>
    <div>
        <div class="username" style="cursor:pointer; font-weight:600; color:#1e40af;">
            <?= htmlspecialchars(trim($p['prenom'].' '.$p['nom'])) ?>
            <?php if(isset($_SESSION['user_id']) && $p['utilisateur_id'] == $_SESSION['user_id']): ?>
                <span style="background:#3b82f6;color:white;padding:3px 8px;border-radius:50px;font-size:11px;margin-left:8px;">Vous</span>
            <?php endif; ?>
        </div>
        <div class="post-time"><?= date('d/m/Y à H:i', strtotime($p['createdAt'])) ?></div>
    </div>
</div>

    <div class="menu-toggle" onclick="toggleMenu(this)">
        <i class="fas fa-ellipsis-h"></i>
    </div>

    <div class="menu-dropdown">
    <?php if (isset($_SESSION['user_id']) && $p['utilisateur_id'] == $_SESSION['user_id']): ?>
        <a href="update.php?id=<?= $p['id'] ?>" class="menu-item"><i class="fas fa-edit"></i> Modifier</a>
        
        <!-- Lien de suppression avec SweetAlert (sans AJAX) -->
        <a href="delete.php?id=<?= $p['id'] ?>" class="menu-item delete" 
           onclick="event.preventDefault(); confirmDeletePost(<?= $p['id'] ?>);">
            <i class="fas fa-trash"></i> Supprimer
        </a>
    <?php else: ?>
        <a href="?fav=<?= $p['id'] ?>&filter=<?= $filter ?? 'toutes' ?>" 
           class="menu-item" style="color:#f59e0b;">
            <i class="fas fa-bookmark<?= in_array($p['id'], $_SESSION['favoris']??[]) ? '' : '-o' ?>"></i>
            <?= in_array($p['id'], $_SESSION['favoris']??[]) ? 'Retirer des favoris' : 'Enregistrer' ?>
        </a>
    <?php endif; ?>
</div>
</div>

                    <div class="post-content">
                        <?= htmlspecialchars($p['contenu']); ?>
                    </div>

                    <?php if (!empty(trim($p['imageUrl'] ?? ''))): ?>
    <div style="margin:16px 0; text-align:center;">
        <img src="../uploads/<?= htmlspecialchars(basename($p['imageUrl'])) ?>" 
             alt="Image de la publication"
             style="max-width:100%; max-height:420px; height:auto; border-radius:16px; 
                    box-shadow:0 6px 20px rgba(0,0,0,0.12); display:block; margin:0 auto;"
             onerror="this.style.display='none';">
    </div>
<?php endif; ?>

                    <div class="interactions">
    <span class="action-btn like-btn <?= $isLiked ? 'liked' : '' ?>" 
          style="cursor:pointer;" 
          data-post-id="<?= $p['id'] ?>">
        <i class="fas fa-heart"></i>
        <span class="like-count"><?= $likesCount ?></span>
    </span>

    <div class="action-btn" onclick="toggleComments(<?= $p['id'] ?>)">
        <i class="far fa-comment"></i> Commenter 
        <?php if($nbComments > 0): ?>
            <span class="comment-count">(<?= $nbComments ?>)</span>
        <?php endif; ?>
    </div>

    <!--  BOUTON TRADUIRE CORRIGÉ AVEC ICÔNE -->
    <!-- Bouton Traduire discret -->
<div class="action-btn translate-btn" 
     data-text="<?= htmlspecialchars($p['contenu'], ENT_QUOTES) ?>"
     data-post-id="<?= $p['id'] ?>">
    <i class="fas fa-language"></i> Traduire
</div>
</div>
<!-- Traduction discrète, juste sous le post original -->
<div class="post-translation" id="trad-<?= $p['id'] ?>" style="display:none; margin:8px 16px 0; padding:8px 12px; background:#f8fafc; border-radius:10px; font-size:13px; color:#6b7280; line-height:1.4; border-left:2px solid #8b5cf6; font-style:italic;"></div>

                    <div class="comments-section" id="comments-<?= $p['id'] ?>">
                        <div class="comments-list">
                            <?php
$comments = $commentaireC->listeCommentaires($p['id']);
foreach ($comments as $c): 
    $commentUserId = $c['utilisateur_id'];
    $isOwnerComment = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $commentUserId;
?>
    <div class="comment" id="comment-<?= $c['id'] ?>">
        <div class="avatar"><?= $avatarSVG ?></div>
        <div class="comment-body">
            <div class="comment-header">
                <div class="comment-info">
                    
    
                    <span class="time"><?= date('H:i', strtotime($c['created_at'])) ?></span>
                </div>

                <?php if ($isOwnerComment): ?>
                    <div class="menu-toggle" onclick="toggleMenu(this)">
                        <i class="fas fa-ellipsis-h"></i>
                    </div>
                    <div class="menu-dropdown">
                        <a href="#" class="menu-item" onclick="editComment(<?= $c['id'] ?>, <?= $p['id'] ?>, `<?= addslashes(htmlspecialchars($c['contenu'])) ?>`); return false;">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <a href="supprimerCommentaire.php?id=<?= $c['id'] ?>&post=<?= $p['id'] ?>" 
                            class="menu-item delete"
                            onclick="event.preventDefault(); confirmDelete(<?= $c['id'] ?>, <?= $p['id'] ?>);">
                                Supprimer
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <p class="comment-text" id="text-<?= $c['id'] ?>">
                <?= nl2br(htmlspecialchars($c['contenu'])) ?>
            </p>

            <form action="modifierCommentaire.php" method="POST" class="edit-form" id="form-<?= $c['id'] ?>" style="display:none; margin-top:10px;">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                <div style="display:flex; gap:8px;">
                    <input type="text" name="contenu" value="<?= htmlspecialchars($c['contenu']) ?>" 
                           style="flex:1; padding:9px 12px; border:1.5px solid #3b82f6; border-radius:12px; font-size:14px;" required>
                    <button type="submit" style="background:#3b82f6; color:white; border:none; padding:0 16px; border-radius:12px; cursor:pointer;">Envoyer</button>
                    <button type="button" onclick="cancelEdit(<?= $c['id'] ?>)" style="background:#e2e8f0; color:#64748b; border:none; padding:0 12px; border-radius:12px; cursor:pointer;">Annuler</button>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>
                        </div>

                        <!-- FORMULAIRE COMMENTAIRE AVEC FILTRE GROS MOTS (CHANGEMENT ICI) -->
                        <form class="comment-form" action="ajouterCommentaire.php" method="POST" onsubmit="return validateComment(event, this)">
                            <input type="hidden" name="blog_id" value="<?= $p['id'] ?>">
                            <div class="avatar"><?= $avatarSVG ?></div>
                            <input type="text" name="contenu" 
                                   placeholder="<?= isset($_SESSION['user_id']) ? 'Ajouter un commentaire...' : 'Connectez-vous pour commenter' ?>"
                                   <?= isset($_SESSION['user_id']) ? '' : 'disabled' ?> 
                                   autocomplete="off" required>
                            <button type="submit" <?= !isset($_SESSION['user_id']) ? 'disabled' : '' ?>>
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <a href="add.php" class="add-post-btn" title="Créer un nouveau post">
        <i class="fas fa-plus"></i>
    </a>

    <script>
        // Like avec alerte connexion
        document.querySelectorAll('.like-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (!estConnecte) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Connexion requise',
                        text: 'Vous devez vous connecter pour aimer une publication',
                        showCancelButton: true,
                        confirmButtonText: 'Se connecter',
                        cancelButtonText: 'Annuler',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'login.php';
                        }
                    });
                } else {
                    const postId = this.getAttribute('data-post-id');
                    window.location.href = 'like.php?post_id=' + postId;
                }
            });
        });

        // FONCTION REMPLACÉE (validateComment au lieu de submitComment)
        async function validateComment(event, form) {
            event.preventDefault();

            if (!estConnecte) {
                Swal.fire('Connexion requise', 'Connectez-vous pour commenter', 'warning')
                    .then(r => { if (r.isConfirmed) location.href = 'login.php'; });
                return;
            }

            const input = form.querySelector('input[name="contenu"]');
            const text = input.value.trim();

            if (text === '') {
                Swal.fire('Oups', 'Le commentaire ne peut pas être vide', 'info');
                return;
            }

            const btn = form.querySelector('button');
            const oldIcon = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            try {
                const response = await fetch('../../Controller/badwords.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ text: text })
                });

                const result = await response.json();

                if (result.bad === true) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Langage inapproprié détecté',
                        html: 'Votre commentaire contient des mots interdits.<br><br><small style="opacity:0.8;">Merci de rester respectueux</small>',
                        confirmButtonText: 'Compris',
                        confirmButtonColor: '#ef4444',
                        timer: 7000,
                        timerProgressBar: true
                    });
                    btn.innerHTML = oldIcon;
                    btn.disabled = false;
                    return;
                }
            } catch (err) {
    console.warn('API badwords indisponible, on laisse passer');
    Swal.fire('Erreur', 'Impossible de vérifier les mots interdits. Veuillez réessayer.', 'error');
    btn.innerHTML = oldIcon;
    btn.disabled = false;
    return; // ← Bloque la soumission si API down
}

            form.submit();
        }

        // SUPPRIMÉE : l'ancienne fonction submitComment (inutile maintenant)

        function toggleMenu(btn) {
            const menu = btn.nextElementSibling;
            document.querySelectorAll('.menu-dropdown.show').forEach(m => {
                if (m !== menu) m.classList.remove('show');
            });
            menu.classList.toggle('show');
        }

        function toggleComments(postId) {
            const section = document.getElementById('comments-' + postId);
            const btn = section.previousElementSibling.querySelector('.action-btn:nth-child(2)');
            section.classList.toggle('open');
            btn.classList.toggle('active');
            if (section.classList.contains('open') && estConnecte) {
                setTimeout(() => {
                    section.querySelector('input[name="contenu"]').focus();
                }, 300);
            }
        }

        function editComment(id, postId, texte) {
            document.getElementById('text-' + id).style.display = 'none';
            document.getElementById('form-' + id).style.display = 'block';
            document.getElementById('form-' + id).querySelector('input[name="contenu"]').focus();
        }

        function cancelEdit(id) {
            document.getElementById('text-' + id).style.display = 'block';
            document.getElementById('form-' + id).style.display = 'none';
        }
        function confirmDelete(commentId, postId) {
        Swal.fire({
            title: 'Supprimer ce commentaire ?',
            text: "Cette action est irréversible !",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            reverseButtons: true
            }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `supprimerCommentaire.php?id=${commentId}&post=${postId}`;
            }
            });
        }
        function confirmDeletePost(postId) {
        Swal.fire({
            title: 'Supprimer ce post ?',
            text: "Tous les commentaires et likes seront aussi supprimés. Cette action est irréversible !",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Oui, supprimer définitivement',
            cancelButtonText: 'Annuler',
            reverseButtons: true
            }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `delete.php?id=${postId}`;
            }
            });
        }
    </script>
    </div>
<script>
// TRADUCTION – Version finale 100% fonctionnelle avec translate.php
document.querySelectorAll('.translate-btn').forEach(btn => {
    btn.addEventListener('click', async function () {
        const text = this.dataset.text;
        const postId = this.dataset.postId;
        const box = document.getElementById('trad-' + postId);

        // Si déjà affiché → on ferme
        if (box.style.display === 'block') {
            box.style.display = 'none';
            this.innerHTML = '<i class="fas fa-language"></i> Traduire';
            return;
        }

        // Loader
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traduction...';

        try {
            const res = await fetch('../../Controllers/translate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text: text })
            });

            const data = await res.json();

            if (data.response) {
                box.innerHTML = data.response
                    .replace(/\n/g, '<br>')
                    .replace(/FR:/g, '<strong style="color:#1e40af;">FR :</strong>')
                    .replace(/EN:/g, '<strong style="color:#dc2626;">EN :</strong>')
                    .replace(/AR:/g, '<strong style="color:#16a34a;">AR :</strong>');
                box.style.display = 'block';
            } else {
                box.innerHTML = '<span style="color:#ef4444;">Erreur de traduction</span>';
                box.style.display = 'block';
            }
        } catch (e) {
    console.error('Erreur traduction:', e);
    box.innerHTML = '<span style="color:#ef4444;">Serveur indisponible<br><small>Vérifie la console pour détails</small></span>';
    box.style.display = 'block';
}

        // Remettre le bouton normal
        this.innerHTML = '<i class="fas fa-language"></i> Traduire';
    });
});
</script>

</body>
</html>