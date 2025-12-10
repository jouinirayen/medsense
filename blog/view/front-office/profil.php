<?php
session_start();
require_once '../../Controller/blogC.php';
require_once '../../Controller/commentaireC.php';
require_once '../../Controller/likeC.php';

$blogC = new blogC();
$commentaireC = new commentaireC();
$likeC = new likeC();

// Récupérer l'ID depuis l'URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: liste.php');
    exit;
}
$user_id = (int)$_GET['id'];

// Récupérer les infos de l'utilisateur
$db = config::getConnexion();
$stmt = $db->prepare("SELECT prenom, nom, created_at FROM utilisateur WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('<h2 style="text-align:center; margin:100px; color:#dc2626;">Utilisateur non trouvé</h2>');
}

$fullName = htmlspecialchars(trim($user['prenom'] . ' ' . $user['nom']));
$dateInscription = date('d/m/Y', strtotime($user['created_at'] ?? 'now'));

// Récupérer ses publications approuvées
$posts = $blogC->getPostsByUserId($user_id); // Assure-toi que cette méthode existe dans blogC

// Avatar bleu fixe
$avatarSVG = '<svg width="100%" height="100%" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
  <circle cx="50" cy="50" r="50" fill="#374151"/>
  <circle cx="50" cy="35" r="18" fill="#9ca3af"/>
</svg>';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil de <?= $fullName ?> - MedSense</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        }
        .container { max-width: 700px; margin: 0 auto; padding: 0 20px; }
        .back-btn {
            position: fixed; top: 20px; left: 20px; background: white; padding: 12px 20px;
            border-radius: 50px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); color: #1e40af;
            text-decoration: none; font-weight: 600; z-index: 1000; backdrop-filter: blur(10px);
        }
        .back-btn:hover { background: #3b82f6; color: white; }

        .profile-header {
            background: white; border-radius: 20px; padding: 30px; text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08); margin-bottom: 30px; border: 1px solid #e2e8f0;
        }
        .profile-avatar {
            width: 130px; height: 130px; border-radius: 50%; margin: 0 auto 20px;
            border: 5px solid #3b82f6; box-shadow: 0 8px 25px rgba(59,130,246,0.25); overflow: hidden;
        }
        .profile-name { font-size: 28px; font-weight: 700; color: #1e40af; margin-bottom: 8px; }
        .profile-info { color: #64748b; font-size: 15px; }

        h2 { text-align: center; color: #1e40af; margin: 30px 0 20px; font-size: 22px; }

        ul { list-style: none; display: flex; flex-direction: column; gap: 16px; }
        li { background: white; border-radius: 18px; box-shadow: 0 4px 16px rgba(0,0,0,.08); border: 1px solid #e2e8f0; overflow: hidden; }
        .post-header { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; position: relative; }
        .user-info { display: flex; align-items: center; gap: 12px; }
        .user-avatar, .avatar { width: 44px; height: 44px; border-radius: 50%; border: 2.5px solid #3b82f6; box-shadow: 0 2px 8px rgba(59,130,246,.2); overflow: hidden; background: #2563eb; flex-shrink: 0; }
        .comment .avatar { width: 34px; height: 34px; }
        .username { font-weight: 600; color: #1e40af; font-size: 15px; cursor: pointer; }
        .post-time { color: #64748b; font-size: 12px; }
        .post-content { padding: 0 16px 12px; font-size: 15px; line-height: 1.5; color: #334155; word-break: break-word; }
        .interactions { display: flex; align-items: center; gap: 24px; padding: 10px 16px; background: white; border-top: 1px solid #e2e8f0; font-size: 13.5px; color: #64748b; }
        .action-btn { display: flex; align-items: center; gap: 6px; font-weight: 500; cursor: pointer; transition: .2s; }
        .action-btn i { font-size: 16px; color: #94a3b8; }
        .action-btn:hover, .action-btn.active { color: #3b82f6; }
        .action-btn:hover i, .action-btn.active i { color: #3b82f6; }
        .action-btn.liked { color: #ef4444 !important; }
        .action-btn.liked i { color: #ef4444 !important; animation: beat 0.6s ease-in-out; }
        @keyframes beat { 0%,100% { transform: scale(1); } 50% { transform: scale(1.4); } }

        .comments-section { max-height: 0; overflow: hidden; transition: max-height .4s ease; background: #f8fafc; }
        .comments-section.open { max-height: 2000px; }
        .comment { display: flex; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
        .comment-body { flex: 1; background: white; padding: 10px 14px; border-radius: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .comment-info strong { color: #1e40af; font-size: 14px; cursor: pointer; }
        .comment-text { font-size: 14px; color: #334155; word-break: break-word; margin: 4px 0 0; }
    </style>
</head>
<body>

<a href="liste.php" class="back-btn">Retour</a>

<div class="container">
    <div class="profile-header">
        <div class="profile-avatar"><?= $avatarSVG ?></div>
        <div class="profile-name"><?= $fullName ?></div>
        <div class="profile-info">Membre depuis le <?= $dateInscription ?> • <?= count($posts) ?> publication<?= count($posts) > 1 ? 's' : '' ?></div>
    </div>

    <h2>Ses publications</h2>

    <?php if (empty($posts)): ?>
        <p style="text-align:center; color:#64748b; font-size:16px; padding:40px;">Aucune publication pour le moment.</p>
    <?php else: ?>
        <ul>
            <?php foreach($posts as $p): 
                $nbComments = count($commentaireC->listeCommentaires($p['id']));
                $likesCount = $likeC->countLikes($p['id']);
                $isLiked = isset($_SESSION['user_id']) && $likeC->isLiked($_SESSION['user_id'], $p['id']);
            ?>
                <li id="post-<?= $p['id'] ?>">
                    <div class="post-header">
                        <div class="user-info">
                            <div class="user-avatar"><?= $avatarSVG ?></div>
                            <div>
                                <div class="username" onclick="window.location.href='profil.php?id=<?= $p['utilisateur_id'] ?>'">
                                    <?= htmlspecialchars(trim($p['prenom'] . ' ' . $p['nom'])) ?>
                                    <?php if (isset($_SESSION['user_id']) && $p['utilisateur_id'] == $_SESSION['user_id']): ?>
                                        <span style="background:#3b82f6;color:white;padding:3px 9px;border-radius:50px;font-size:11px;margin-left:8px;">Vous</span>
                                    <?php endif; ?>
                                </div>
                                <div class="post-time"><?= date('d/m/Y à H:i', strtotime($p['createdAt'])); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="post-content"><?= nl2br(htmlspecialchars($p['contenu'])) ?></div>

                    <?php if (!empty($p['imageUrl'])): ?>
                        <div style="margin:16px 0; text-align:center;">
                            <img src="../../<?= htmlspecialchars($p['imageUrl']) ?>" style="max-width:100%; max-height:380px; border-radius:16px; box-shadow:0 6px 20px rgba(0,0,0,0.12);" onerror="this.style.display='none'">
                        </div>
                    <?php endif; ?>

                    <div class="interactions">
                        <span class="action-btn like-btn <?= $isLiked ? 'liked' : '' ?>" style="cursor:pointer;" data-post-id="<?= $p['id'] ?>">
                            <i class="fas fa-heart"></i> <span class="like-count"><?= $likesCount ?></span>
                        </span>
                        <div class="action-btn" onclick="document.getElementById('comments-<?= $p['id'] ?>').classList.toggle('open')">
                            <i class="far fa-comment"></i> Commenter <?= $nbComments > 0 ? "<span class='comment-count'>($nbComments)</span>" : '' ?>
                        </div>
                    </div>

                    <div class="comments-section" id="comments-<?= $p['id'] ?>">
                        <div class="comments-list">
                            <?php foreach($commentaireC->listeCommentaires($p['id']) as $c): ?>
                                <div class="comment">
                                    <div class="avatar"><?= $avatarSVG ?></div>
                                    <div class="comment-body">
                                        <div class="comment-info">
                                            <strong onclick="window.location.href='profil.php?id=<?= $c['utilisateur_id'] ?>'">
                                                <?= htmlspecialchars(trim(($c['prenom']??'').' '.($c['nom']??'')) ?: 'Anonyme') ?>
                                            </strong>
                                            <span class="time"><?= date('H:i', strtotime($c['created_at'])) ?></span>
                                        </div>
                                        <p class="comment-text"><?= nl2br(htmlspecialchars($c['contenu'])) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<script>
    const estConnecte = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!estConnecte) {
                Swal.fire('Connexion requise', 'Connectez-vous pour liker', 'warning');
                return;
            }
            window.location.href = 'like.php?post_id=' + this.dataset.postId;
        });
    });
</script>
</body>
</html>