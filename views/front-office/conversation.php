<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['with']) || !is_numeric($_GET['with'])) {
    header('Location: messages.php');
    exit;
}

$destinataire_id = (int)$_GET['with'];
$user_id = $_SESSION['user_id'];

$db = config::getConnexion();

// Récupérer le destinataire
$stmt = $db->prepare("SELECT id, prenom, nom FROM utilisateur WHERE id = ?");
$stmt->execute([$destinataire_id]);
$destinataire = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$destinataire) {
    die("Utilisateur introuvable");
}

$destinataire_name = htmlspecialchars($destinataire['prenom'] . ' ' . $destinataire['nom']);

// --- LOGIQUE POUR LA SIDEBAR ET LES NOTIFICATIONS ---

// 1. Marquer les messages de la conversation actuelle comme lus
$stmt_mark_as_read = $db->prepare("
    UPDATE messages 
    SET lu = 1 
    WHERE expediteur_id = :destinataire_id AND destinataire_id = :user_id AND lu = 0
");
$stmt_mark_as_read->execute([
    'destinataire_id' => $destinataire_id, 
    'user_id' => $user_id
]);

// 2. Récupérer tous les utilisateurs AVEC les statistiques de messages pour le tri
$sql_users_with_stats = "
    SELECT 
        u.id, 
        u.prenom, 
        u.nom,
        -- Compter les messages non lus envoyés PAR l'AUTRE utilisateur
        (SELECT COUNT(m.id) 
         FROM messages m 
         WHERE m.expediteur_id = u.id AND m.destinataire_id = :user_id AND m.lu = 0) AS non_lus,
         
        -- Trouver la date du dernier message pour le tri
        (SELECT MAX(m.date_envoi) 
         FROM messages m 
         WHERE (m.expediteur_id = :user_id AND m.destinataire_id = u.id) 
            OR (m.expediteur_id = u.id AND m.destinataire_id = :user_id)
        ) AS last_message_date
    FROM 
        utilisateur u 
    WHERE 
        u.id != :user_id
    -- Tri: Priorité aux messages non lus, puis par date d'activité la plus récente
    ORDER BY 
        CASE WHEN non_lus > 0 THEN 0 ELSE 1 END, 
        last_message_date DESC, 
        u.prenom ASC
";

$stmt_users = $db->prepare($sql_users_with_stats);
$stmt_users->execute(['user_id' => $user_id]);
$all_users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// 3. Compter le total des messages non lus pour l'icône de la barre de navigation
$stmt_total_unread = $db->prepare("
    SELECT COUNT(id) AS total_non_lus 
    FROM messages 
    WHERE destinataire_id = :user_id AND lu = 0
");
$stmt_total_unread->execute(['user_id' => $user_id]);
$total_non_lus = $stmt_total_unread->fetchColumn();

// --- FIN LOGIQUE POUR LA SIDEBAR ET LES NOTIFICATIONS ---

// Avatar bleu fixe
$avatarSVG = '<svg width="100%" height="100%" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
  <circle cx="50" cy="50" r="50" fill="#374151"/>
  <circle cx="50" cy="35" r="18" fill="#9ca3af"/>
</svg>';

// Envoi de message
if ($_POST && !empty($_POST['message'])) {
    $contenu = trim($_POST['message']);
    if ($contenu !== '') {
        $stmt = $db->prepare("INSERT INTO messages (expediteur_id, destinataire_id, contenu) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $destinataire_id, $contenu]);
    }
    // Redirection pour éviter la soumission multiple et mettre à jour la page
    header("Location: conversation.php?with=$destinataire_id");
    exit;
}

// Récupérer les messages
$stmt = $db->prepare("
    SELECT m.*, u.prenom, u.nom 
    FROM messages m 
    LEFT JOIN utilisateur u ON m.expediteur_id = u.id 
    WHERE (m.expediteur_id = ? AND m.destinataire_id = ?) 
        OR (m.expediteur_id = ? AND m.destinataire_id = ?) 
    ORDER BY m.date_envoi ASC
");
$stmt->execute([$user_id, $destinataire_id, $destinataire_id, $user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Logique pour le nom d'utilisateur connecté dans le header
$nomUtilisateurConnecte = "Invité";
if (isset($_SESSION['user_id']) && isset($_SESSION['prenom']) && isset($_SESSION['nom'])) {
    $nomUtilisateurConnecte = trim($_SESSION['prenom'] . " " . $_SESSION['nom']);
} elseif (isset($_SESSION['email'])) {
    $nomUtilisateurConnecte = $_SESSION['email'];
}
// Récupération des utilisateurs pour les suggestions de recherche (basée sur liste.php)
$sql_search = "SELECT DISTINCT prenom, nom FROM utilisateur ORDER BY prenom, nom";
$req_search = $db->prepare($sql_search);
$req_search->execute();
$all_users_search = $req_search->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Conversation avec <?= $destinataire_name ?> - MedSense</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #fff 0%, #f0f9ff 50%, #e0f2fe 100%); 
            color: #1e293b; 
            min-height: 100vh; 
            padding: 0;
            position: relative;
        }

        /* Styles du HEADER */
        .header {
            background: rgba(255,255,255,.95); backdrop-filter: blur(20px);
            padding: 16px 40px; display: flex; justify-content: space-between;
            align-items: center; position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 20px rgba(0,0,0,.04); border-bottom: 1px solid rgba(147,197,253,.2);
        }
        .logo img { width: 200px; }
        .avatar, .user-avatar {
            width: 44px; height: 44px; border-radius: 50%; 
            border: 2.5px solid #3b82f6; box-shadow: 0 2px 8px rgba(59,130,246,.2);
            overflow: hidden; background: #2563eb; flex-shrink: 0;
        }
        .search-suggestions {
            position: absolute; top: 100%; left: 0; right: 0;
            background: white; border: 1px solid #e2e8f0; border-radius: 16px;
            max-height: 380px; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.12);
            z-index: 1000; margin-top: 8px; display: none;
        }
        .suggestion-item {
            padding: 14px 20px; cursor: pointer; transition: background 0.2s;
            border-bottom: 1px solid #f1f5f9; font-size: 15px; color: #334155;
        }
        .suggestion-item:hover { background: #f0f9ff; color: #1e40af; font-weight: 600; }
        .suggestion-item:last-child { border-bottom: none; }


        /* Styles de la SIDEBAR */
        .sidebar {
            position: fixed; left: 0; top: 0; bottom: 0; width: 280px;
            background: rgba(255,255,255,0.94); backdrop-filter: blur(16px);
            border-right: 1px solid rgba(0,0,0,0.08); padding: 100px 20px 20px;
            overflow-y: auto; z-index: 50;
        }
        .sidebar h3 {
            font-size: 11px; text-transform: uppercase; color: #64748b;
            margin: 20px 0 12px; letter-spacing: 1px; font-weight: 600;
            padding-left: 16px;
        }
        .user-item {
            display: flex; align-items: center; gap: 12px; padding: 12px 16px;
            border-radius: 12px; cursor: pointer; transition: 0.2s;
            text-decoration: none; color: #334155;
        }
        .user-item:hover { background: rgba(59,130,246,0.12); }
        .user-item.active { 
            background: #3b82f6; 
            color: white; 
            box-shadow: 0 4px 10px rgba(59,130,246,0.3);
        }
        .user-avatar-small { 
            width: 40px; height: 40px; border-radius: 50%; 
            border: 2px solid #3b82f6; overflow: hidden; background: #2563eb; 
            flex-shrink: 0; 
            transition: border-color 0.2s; /* Pour l'effet notification */
        }
        .user-item.active .user-avatar-small {
            border-color: white !important; /* Bordure blanche quand actif */
        }
        .user-item[style*="border-color: #ef4444"] .user-avatar-small {
            border-color: #ef4444 !important; /* Bordure rouge pour la notification */
        }

        /* Styles spécifiques à la conversation (Améliorés) */
        .main-content {
            margin-left: 280px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 80px);
        }
        .chat-container {
            max-width: 800px; /* MODIFIÉ : Largeur fixe */
            width: 100%; /* S'assure qu'il prend 100% de la place disponible jusqu'à 800px */
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            overflow: hidden;
            height: calc(100vh - 120px); /* MODIFIÉ : Hauteur fixe */
            display: flex;
            flex-direction: column;
        }
        
        /* En-tête de la conversation (pour le nom du destinataire) */
        .chat-header {
            padding: 15px 30px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .chat-header h2 {
            font-size: 20px;
            color: #1e40af;
            font-weight: 700;
        }

        .messages-area {
            flex: 1; padding: 30px; overflow-y: auto; background: #f8fafc;
        }
        .message {
            margin-bottom: 25px;
            display: flex; gap: 12px;
            max-width: 75%;
        }
        
        /* Mon style (Moi) */
        .message.moi { 
            margin-left: auto; 
            flex-direction: row-reverse; 
            text-align: right;
        }

        /* Bulle de message */
        .message-bubble {
            padding: 14px 18px;
            border-radius: 20px;
            border-bottom-left-radius: 4px; 
            background: #e0e7ff; 
            color: #1e293b; 
            max-width: 100%;
            line-height: 1.5;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        /* Bulle de message (Moi) */
        .message.moi .message-bubble { 
            background: #3b82f6; 
            color: white;
            border-bottom-left-radius: 20px; 
            border-bottom-right-radius: 4px;
        }

        .message-time {
            font-size: 11px; 
            color: #94a3b8;
            margin-top: 4px; 
            text-align: left;
        }
        .message.moi .message-time {
            text-align: right;
        }


        .message-input-area {
            padding: 20px; background: white; border-top: 1px solid #e2e8f0;
            display: flex; gap: 15px;
        }
        .message-input {
            flex: 1; padding: 16px 20px; border: 2px solid #e2e8f0;
            border-radius: 30px; font-size: 16px; outline: none;
            transition: all 0.2s;
        }
        .message-input:focus {
            border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59,130,246,0.15);
        }
        .send-btn {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white; border: none; border-radius: 50%;
            cursor: pointer; font-size: 20px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 6px 16px rgba(59,130,246,0.4);
            transition: transform 0.2s;
        }
        .send-btn:hover { transform: scale(1.05); }

        @media (max-width: 992px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

    <header class="header">
        <div style="display: flex; align-items: center; gap: 20px; flex: 1;">
            <div class="logo">
                <img src="http://localhost/blog/logo.png" alt="logo">
            </div>

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

            <div style="position: absolute; right: 520px; top: 50%; transform: translateY(-50%); display: flex; gap: 25px; align-items: center; z-index: 1000; left: 990px;">
                
                <a href="liste.php" 
                style="color: #64748b; font-size: 26px; transition: color 0.2s; text-decoration: none;" 
                onmouseover="this.style.color='#3b82f6'" 
                onmouseout="this.style.color='#64748b'" 
                title="Accueil">
                    <i class="fas fa-house"></i>
                </a>

                <a href="messages.php" 
                    style="color: #3b82f6; font-size: 26px; transition: color 0.2s; text-decoration: none; position: relative;" 
                    title="Messages">
                    <i class="fas fa-envelope"></i>
                    <?php if ($total_non_lus > 0): ?>
                        <span style="position: absolute; top: -5px; right: -10px; background: #ef4444; color: white; border-radius: 50%; font-size: 12px; font-weight: 700; padding: 2px 6px; line-height: 1;">
                            <?= $total_non_lus > 9 ? '9+' : $total_non_lus ?>
                        </span>
                    <?php endif; ?>
                </a>

                <a href="messages.php" 
                    style="color: #64748b; font-size: 26px; transition: color 0.2s; text-decoration: none;" 
                    onmouseover="this.style.color='#3b82f6'" 
                    onmouseout="this.style.color='#64748b'" 
                    title="Retour aux messages">
                    
                </a>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:20px;">
            <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['prenom']) && !empty($_SESSION['nom'])): ?>
                <div style="text-align:right; line-height:1.4;">
                    <strong style="font-size:16px; color:#1e40af;">
                        <?= $nomUtilisateurConnecte ?>
                    </strong><br>
                    
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

    <div class="sidebar">
        <h3>Conversations</h3>
        <?php foreach($all_users as $user): 
            $fullName = trim($user['prenom'] . ' ' . $user['nom']);
            if(empty($fullName)) continue;
            $unread_count = $user['non_lus'] ?? 0;
        ?>
            <a href="conversation.php?with=<?= $user['id'] ?>" 
               class="user-item <?= $user['id'] == $destinataire_id ? 'active' : '' ?>"
               style="position: relative;">

                <div class="user-avatar-small" style="<?= $unread_count > 0 ? 'border-color: #ef4444;' : '' ?>"><?= $avatarSVG ?></div>
                
                <div style="font-weight:<?= $unread_count > 0 ? '700; color: #1e293b;' : '600;' ?>; flex-grow: 1;">
                    <?= htmlspecialchars($fullName) ?>
                </div>

                <?php if ($unread_count > 0): ?>
                    <span style="background: #ef4444; color: white; border-radius: 10px; font-size: 11px; font-weight: 700; padding: 3px 8px; min-width: 25px; text-align: center;">
                        <?= $unread_count > 9 ? '9+' : $unread_count ?>
                    </span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="main-content">
        <div class="chat-container">

            <div class="chat-header">
                <div style="width:40px; height:40px; flex-shrink:0; border-radius:50%; overflow:hidden;">
                    <?= $avatarSVG ?>
                </div>
                <h2><?= $destinataire_name ?></h2>
            </div>

            <div class="messages-area" id="messagesArea">
                <?php if (empty($messages)): ?>
                    <div style="text-align:center; padding:50px 20px; color:#94a3b8;">
                        <i class="fas fa-comment-dots" style="font-size:80px; margin-bottom:30px; color:#cbd5e1;"></i>
                        <p style="font-size:20px;">Démarrez une nouvelle conversation</p>
                        <small>Votre historique de messages sera affiché ici.</small>
                    </div>
                <?php else: ?>
                    <?php foreach($messages as $m): 
                        $isMe = $m['expediteur_id'] == $user_id;
                    ?>
                        <div class="message <?= $isMe ? 'moi' : '' ?>">
                            <?php if (!$isMe): ?>
                                <div style="width:40px; height:40px; flex-shrink:0; align-self: flex-end;"><?= $avatarSVG ?></div>
                            <?php endif; ?>
                            
                            <div>
                                <div class="message-bubble">
                                    <?= nl2br(htmlspecialchars($m['contenu'])) ?>
                                </div>
                                <div class="message-time">
                                    <?= date('H:i', strtotime($m['date_envoi'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form method="POST" class="message-input-area">
                <input type="text" name="message" class="message-input" placeholder="Écrire un message..." required autocomplete="off">
                <button type="submit" class="send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>

    <script>
        // Scroll automatique en bas
        const messagesArea = document.getElementById('messagesArea');
        messagesArea.scrollTop = messagesArea.scrollHeight;

        // Code JavaScript pour la barre de recherche (copié de liste.php)
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
            if (searchInput.value.trim() === '') {
                suggestionItems.forEach(item => item.style.display = 'block');
                suggestionsBox.style.display = 'block';
            } else {
                showSuggestions();
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

        searchForm.addEventListener('submit', function() {
            suggestionsBox.style.display = 'none';
        });
    </script>
</body>
</html>