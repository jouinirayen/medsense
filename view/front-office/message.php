<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Avatar bleu fixe
$avatarSVG = '<svg width="100%" height="100%" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
  <circle cx="50" cy="50" r="50" fill="#374151"/>
  <circle cx="50" cy="35" r="18" fill="#9ca3af"/>
</svg>';

// Tous les utilisateurs sauf moi → pour la sidebar
$db = config::getConnexion();
$stmt = $db->prepare("SELECT id, prenom, nom FROM utilisateur WHERE id != ? ORDER BY prenom, nom");
$stmt->execute([$user_id]);
$all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tous les utilisateurs → pour la recherche (comme dans liste.php)
$stmt2 = $db->prepare("SELECT DISTINCT prenom, nom FROM utilisateur ORDER BY prenom, nom");
$stmt2->execute();
$all_users_search = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messages - MedSense</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
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

        /* HEADER 100% IDENTIQUE À liste.php (positions, marges, tout) */
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
        .avatar { width: 44px; height: 44px; border-radius: 50%; border: 2.5px solid #3b82f6; box-shadow: 0 2px 8px rgba(59,130,246,.2); overflow: hidden; background: #2563eb; flex-shrink: 0; }

        /* Recherche */
        .search-suggestions {
            position: absolute;
            top: 100%; left: 0; right: 0;
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
        .suggestion-item:last-child { border-bottom: none; }

        /* Sidebar messages */
        .sidebar {
            position: fixed;
            left: 0; top: 0; bottom: 0;
            width: 280px;
            background: rgba(255,255,255,0.94);
            backdrop-filter: blur(16px);
            border-right: 1px solid rgba(0,0,0,0.08);
            padding: 100px 20px 40px;
            overflow-y: auto;
            z-index: 50;
        }
        .sidebar h3 {
            font-size: 11px; text-transform: uppercase; color: #64748b;
            margin: 30px 0 12px; letter-spacing: 1px; font-weight: 600; padding-left: 16px;
        }
        .user-item {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; border-radius: 12px;
            text-decoration: none; color: #334155;
            transition: .2s;
        }
        .user-item:hover { background: rgba(59,130,246,.12); }
        .user-avatar-small { width: 40px; height: 40px; border-radius: 50%; border: 2px solid #3b82f6; overflow: hidden; background: #2563eb; }

        .main-content {
            margin-left: 280px;
            padding-top: 20px;
        }
        @media (max-width: 992px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
        }

        .empty-state {
            text-align: center;
            padding: 100px 20px;
            color: #64748b;
            background: white;
            border-radius: 18px;
            box-shadow: 0 4px 16px rgba(0,0,0,.08);
            border: 1px solid #e2e8f0;
            max-width: 700px;
            margin: 40px auto;
        }
        .empty-state i { font-size: 80px; color: #cbd5e1; margin-bottom: 20px; }
    </style>
</head>
<body>

<!-- HEADER EXACTEMENT LE MÊME QUE liste.php -->
<header class="header">
    <div style="display: flex; align-items: center; gap: 20px; flex: 1;">
        <div class="logo">
            <img src="http://localhost/blog/logo.png" alt="logo">
        </div>

        <!-- Barre de recherche (identique) -->
        <div style="position: relative; max-width: 500px; width: 100%; margin-left: 200px;">
            <form id="searchForm" method="GET" action="liste.php" style="position: relative;">
                <input type="text"
                       name="search_user"
                       id="searchInput"
                       value="<?= isset($_GET['search_user']) ? htmlspecialchars($_GET['search_user']) : '' ?>"
                       placeholder="Rechercher un utilisateur..."
                       autocomplete="off"
                       style="width: 100%; padding: 12px 50px 12px 20px; border: 2px solid #e2e8f0; border-radius: 30px; font-size: 15px; outline: none; transition: all .2s; background: white;"
                       onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 4px rgba(59,130,246,0.15)';"
                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">

                <button type="submit" id="searchButton" style="position: absolute; right: 6px; top: 4px; background: #3b82f6; color: white; border: none; width: 38px; height: 38px; border-radius: 50%; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-search"></i>
                </button>
            </form>

            <div id="searchSuggestions" class="search-suggestions">
                <?php foreach($all_users_search as $u):
                    $full_name = trim($u['prenom'] . ' ' . $u['nom']);
                    if(empty($full_name)) continue;
                ?>
                    <div class="suggestion-item" data-name="<?= htmlspecialchars($full_name) ?>">
                        <?= htmlspecialchars($full_name) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Icônes Accueil + Messages (POSITION EXACTE) -->
        <div style="position: absolute; right: 520px; top: 50%; transform: translateY(-50%); display: flex; gap: 25px; align-items: center; z-index: 1000; left: 990px;">
            <a href="liste.php"
               style="color: #64748b; font-size: 26px; transition: color 0.2s;"
               onmouseover="this.style.color='#3b82f6'"
               onmouseout="this.style.color='#64748b'">
                <i class="fas fa-house"></i>
            </a>
            <a href="message.php"
               style="color: #3b82f6; font-size: 26px;">
                <i class="fas fa-envelope"></i>
            </a>
        </div>
    </div>

    <!-- Droite : nom + avatar + déconnexion -->
    <div style="display:flex; align-items:center; gap:20px;">
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
    </div>
</header>

<!-- SIDEBAR -->
<div class="sidebar">
    <h3>Messages</h3>
    <?php foreach($all_users as $user):
        $fullName = trim($user['prenom'] . ' ' . $user['nom']);
        if(empty($fullName)) continue;
    ?>
        <a href="conversation.php?with=<?= $user['id'] ?>" class="user-item">
            <div class="user-avatar-small"><?= $avatarSVG ?></div>
            <div style="font-weight:600;"><?= htmlspecialchars($fullName) ?></div>
        </a>
    <?php endforeach; ?>
</div>

<!-- CONTENU -->
<div class="main-content">
    <div class="empty-state">
        <i class="fas fa-comments"></i>
        <p style="font-size: 22px; margin: 20px 0;">Sélectionne un utilisateur à gauche</p>
        <small>pour commencer une conversation privée</small>
    </div>
</div>

<!-- Script recherche (100% fonctionnel) -->
<script>
    const searchInput = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('searchSuggestions');
    const suggestionItems = document.querySelectorAll('.suggestion-item');
    const searchForm = document.getElementById('searchForm');
    const searchButton = document.getElementById('searchButton');

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

    searchInput.addEventListener('focus', function() {
        if (this.value.trim() === '') {
            suggestionItems.forEach(item => item.style.display = 'block');
            suggestionsBox.style.display = 'block';
        }
    });

    searchInput.addEventListener('input', showSuggestions);

    searchButton.addEventListener('click', function(e) {
        e.preventDefault();
        showSuggestions();
        searchInput.focus();
    });

    suggestionItems.forEach(item => {
        item.addEventListener('click', function() {
            searchInput.value = this.getAttribute('data-name');
            suggestionsBox.style.display = 'none';
            searchForm.submit();
        });
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target) && e.target !== searchButton) {
            suggestionsBox.style.display = 'none';
        }
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            suggestionsBox.style.display = 'none';
            searchForm.submit();
        }
    });
</script>

</body>
</html>