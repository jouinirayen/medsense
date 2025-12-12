<?php
session_start();
// Vérification d'authentification
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// L'avatar bleu sera réutilisé, mais on lui donnera un nom de classe pour le style CSS
$avatarSVG = '<svg class="avatar-svg" width="100%" height="100%" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
  <circle cx="50" cy="50" r="50" fill="#374151"/>
  <circle cx="50" cy="35" r="18" fill="#9ca3af"/>
</svg>';

// Inclusion des contrôleurs
require_once '../../Controller/blogC.php';
require_once '../../Controller/commentaireC.php';

$blogC = new blogC();
$commentaireC = new commentaireC();

// === ACTIONS ===
if ($_POST && isset($_POST['action']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    if ($_POST['action'] === 'approuver_post')         $blogC->approuverPost($id);
    elseif ($_POST['action'] === 'refuser_post')       $blogC->refuserPost($id);
    elseif ($_POST['action'] === 'supprimer_post')     $blogC->deletePost($id);

    elseif ($_POST['action'] === 'approuver_comment')  $commentaireC->approuverCommentaire($id);
    elseif ($_POST['action'] === 'refuser_comment')    $commentaireC->refuserCommentaire($id);
    elseif ($_POST['action'] === 'supprimer_comment')  $commentaireC->supprimerCommentaire($id);

    header("Location: dashboard.php");
    exit;
}

$postsEnAttente = $blogC->compterEnAttente();
$posts          = $blogC->listeTousPostsAdmin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - MedSense</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        /* BASE */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f7f9fc 0%, #eef4ff 100%); background-attachment: fixed; color: #1e293b; min-height: 100vh; padding: 20px 0; }
        .container { max-width: 1100px; margin: 0 auto; padding: 0 20px; }
        
        /* HEADER */
        .header { 
            background: rgba(255, 255, 255, .95); backdrop-filter: blur(20px); padding: 16px 40px; 
            display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; 
            box-shadow: 0 2px 20px rgba(0, 0, 0, .04); border-bottom: 1px solid rgba(147, 197, 253, .2); 
            margin: -20px -20px 40px; 
        }
        .logo img { width: 160px; height: auto; } /* Taille ajustée */
        .admin-profile { display: flex; align-items: center; gap: 20px; }
        .admin-profile-text { color: #64748b; font-weight: 500; font-size: 14px; }
        .avatar-wrapper { 
            width: 44px; height: 44px; border-radius: 50%; 
            border: 3px solid #3b82f6; box-shadow: 0 4px 12px rgba(59, 130, 246, .3); 
            overflow: hidden; 
        }
        .logout-link { color: #94a3b8; text-decoration: none; font-size: 14px; transition: color .2s; }
        .logout-link:hover { color: #ef4444; }

        /* TITRES */
        h1 { text-align: center; color: #1e40af; margin: 20px 0 40px; font-size: 32px; font-weight: 700; font-family: 'Poppins', sans-serif; }
        .section-title { margin: 30px 0 20px; color: #1e40af; font-size: 24px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }

        /* NAVIGATION/ONGLETS */
        .tabs { display: flex; gap: 16px; margin-bottom: 30px; justify-content: center; }
        .tabs a { 
            padding: 14px 32px; background: white; color: #64748b; border-radius: 16px; 
            text-decoration: none; font-weight: 600; box-shadow: 0 4px 15px rgba(0, 0, 0, .06); 
            transition: .3s; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px;
        }
        .tabs a.active, .tabs a:hover { 
            background: #3b82f6; color: white; transform: translateY(-3px); 
            box-shadow: 0 8px 25px rgba(59, 130, 246, .3); border-color: #3b82f6; 
        }
        .tab-badge { 
            background: #ef4444; color: white; padding: 4px 10px; border-radius: 12px; 
            font-size: 12px; font-weight: 700; animation: pulse 2s infinite; 
        }
        @keyframes pulse { 0%, 100% { transform: scale(1) } 50% { transform: scale(1.15) } }

        /* CARTES DE PUBLICATION */
        .post-card { 
            background: white; border-radius: 18px; padding: 28px; margin-bottom: 24px; 
            box-shadow: 0 8px 30px rgba(0, 0, 0, .08); border: 1px solid #f0f4f8; transition: border .2s;
        }
        .post-card:hover { border-color: #3b82f6; }
        .post-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .user-info { display: flex; align-items: center; gap: 12px; font-weight: 600; color: #1e293b; }
        .user-avatar { 
            width: 36px; height: 36px; border-radius: 50%; border: 2px solid #3b82f6; 
            overflow: hidden; flex-shrink: 0; 
        }
        .post-date { font-size: 13px; color: #64748b; }
        .post-content { 
            margin: 16px 0; line-height: 1.7; font-size: 16px; min-height: 20px; 
            color: #334155; 
        }
        .post-image-container { margin: 20px 0; }
        .post-image { 
            max-width: 100%; max-height: 400px; border-radius: 12px; 
            border: 3px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.15); 
            object-fit: cover; width: 100%; height: auto; 
        }

        /* STATUTS */
        .status-badge { padding: 8px 16px; border-radius: 30px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-en_attente { background: #fffbeb; color: #d97706; border: 1px solid #fcd34d; }
        .status-approuve, .status-approuvée, .status-approved { background: #ecfdf5; color: #059669; border: 1px solid #34d399; }
        .status-refuse { background: #fef2f2; color: #dc2626; border: 1px solid #f87171; }

        /* ACTIONS */
        .action-buttons { display: flex; gap: 10px; }
        .action-buttons button { 
            padding: 12px 24px; border: none; border-radius: 14px; color: white; 
            font-weight: 600; cursor: pointer; transition: .2s ease-in-out; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, .1); 
            display: flex; align-items: center; gap: 6px; font-size: 14px;
        }
        .btn-approve { background: linear-gradient(45deg, #10b981, #059669); }
        .btn-approve:hover { background: #059669; transform: translateY(-2px); box-shadow: 0 6px 18px rgba(5, 150, 105, .4); }
        .btn-refuse { background: linear-gradient(45deg, #f97316, #ea580c); } /* Changé la couleur Refuser pour éviter confusion avec Supprimer */
        .btn-refuse:hover { background: #ea580c; transform: translateY(-2px); box-shadow: 0 6px 18px rgba(234, 88, 12, .4); }
        .btn-delete { background: #dc2626 !important; }
        .btn-delete:hover { background: #b91c1c !important; transform: translateY(-2px); box-shadow: 0 6px 18px rgba(220, 38, 38, .4); }

        /* COMMENTAIRES */
        .comments-section { 
            background: #f8fafc; border-radius: 12px; padding: 20px; 
            margin: 25px 0 10px; border: 1px solid #e2e8f0; 
        }
        .comments-header { color: #1e40af; font-size: 16px; margin-bottom: 15px; font-weight: 700; }
        .comment-item { 
            background: white; padding: 14px; border-radius: 10px; margin-bottom: 10px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #f1f5f9;
        }
        .comment-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .comment-author { color: #1e40af; font-size: 13.5px; font-weight: 600; }
        .comment-date { font-size: 11px; color: #64748b; }
        .comment-content { font-size: 14px; color: #334155; margin-bottom: 8px; }
        .comment-actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .comment-actions button, .comment-status-text { 
            padding: 6px 12px; border: none; border-radius: 8px; font-size: 12px; cursor: pointer; 
            font-weight: 600; transition: background .2s;
        }
        .btn-comment-approve { background: #10b981; color: white; }
        .btn-comment-refuse { background: #f97316; color: white; }
        .btn-comment-delete { background: #dc2626; color: white; }
        .comment-status-text { background: #ecfdf5; color: #059669; border: 1px solid #34d399; }

    </style>
</head>
<body>
    <header class="header">
        <div class="logo"><img src="http://localhost/blog/logo.png" alt="MedSense"></div>
        <div class="admin-profile">
            <span class="admin-profile-text">Admin</span>
            <div class="avatar-wrapper">
                <?= $avatarSVG ?>
            </div>
            <a href="login.php?logout=1" class="logout-link"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </header>

    <div class="container">
        <h1>Tableau de bord - Modération</h1>

        <div class="tabs">
            <a href="dashboard.php" class="active">
                <i class="fas fa-newspaper"></i> Publications (<?= count($posts) ?>)
                <?php if ($postsEnAttente > 0): ?><span class="tab-badge"><?= $postsEnAttente ?> en attente</span><?php endif; ?>
            </a>
        </div>
        
        <h2 class="section-title">Toutes les publications</h2>

        <?php if (empty($posts)): ?>
            <div style="text-align:center;padding:60px;color:#64748b;background:white;border-radius:18px;box-shadow:0 8px 30px rgba(0, 0, 0, .05);">
                <i class="fas fa-check-circle fa-4x" style="color:#059669;"></i>
                <p style="margin-top:20px;font-size:18px;font-weight:500;">Aucune publication à modérer pour le moment. Tout est en ordre !</p>
            </div>
        <?php else: ?>
            <?php foreach($posts as $p): ?>
                
                <?php 
// Chemin correct pour afficher les images upload depuis n'importe où dans le projet
$rawPath = $p['imageUrl'] ?? '';

// Nettoyage et normalisation du chemin
$rawPath = trim($rawPath);

// Cas 1 : lien externe (déjà http/https)
if (strpos($rawPath, 'http://') === 0 || strpos($rawPath, 'https://') === 0) {
    $displayPath = $rawPath;
}
// Cas 2 : chemin relatif qui commence déjà par uploads/
elseif (strpos($rawPath, 'uploads/') === 0) {
    $displayPath = '/blog/view/' . $rawPath;
}
// Cas 3 : juste le nom du fichier (ex: "photo.jpg" ou "dossier/photo.jpg")
else {
    $displayPath = '/blog/view/uploads/' . $rawPath;
}
?>

                <div class="post-card">
                    <div class="post-header">
                        <strong class="user-info">
                            <div class="user-avatar">
                                <?= $avatarSVG ?>
                            </div>
                            <?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?>
                        </strong>
                        <span class="post-date"><i class="far fa-clock"></i> <?= date('d/m/Y à H:i', strtotime($p['createdAt'])) ?></span>
                    </div>

                    <div class="post-content">
                        <?= nl2br(htmlspecialchars($p['contenu'])) ?>
                    </div>
                    
                    <?php if (!empty($p['imageUrl'])): ?>
                        <div class="post-image-container">
                            <img src="<?= htmlspecialchars($displayPath) ?>" 
                                alt="Image de la publication" 
                                class="post-image"
                                style="border-color: <?= strtolower($p['statut'] ?? 'en_attente') === 'en_attente' ? '#fcd34d' : '#3b82f6' ?>;"
                                onerror="this.style.border='4px dashed #dc2626'; this.style.borderColor='#dc2626';">
                        </div>
                    <?php else: ?>
                        <p style="color: #94a3b8; font-style: italic; margin: 16px 0 25px;">Aucune image jointe</p>
                    <?php endif; ?>

                    <?php 
                    $commentairesPost = $commentaireC->listeTousCommentairesDuPost($p['id'] ?? 0);
                    if (!empty($commentairesPost)): 
                    ?>
                        <div class="comments-section">
                            <div class="comments-header">
                                <i class="fas fa-comment-dots"></i> Commentaires (<?= count($commentairesPost) ?>)
                            </div>
                            <?php foreach($commentairesPost as $c): ?>
                                <div class="comment-item">
                                    <div class="comment-meta">
                                        <strong class="comment-author">
                                            <i class="fas fa-user-circle"></i> <?= htmlspecialchars(($c['prenom']??'').' '.($c['nom']??'')) ?>
                                        </strong>
                                        <span class="comment-date"><i class="far fa-calendar-alt"></i> <?= date('d/m/Y à H:i', strtotime($c['created_at'])) ?></span>
                                    </div>
                                    <div class="comment-content">
                                        <?= nl2br(htmlspecialchars($c['contenu'])) ?>
                                    </div>
                                    <div class="comment-actions">
                                        <?php $commentStatus = strtolower($c['statut'] ?? 'en_attente'); ?>
                                        <?php if($commentStatus === 'en_attente'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                                <input type="hidden" name="action" value="approuver_comment">
                                                <button type="submit" class="btn-comment-approve"><i class="fas fa-check"></i> Approuver</button>
                                            </form>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                                <input type="hidden" name="action" value="refuser_comment">
                                                <button type="submit" class="btn-comment-refuse"><i class="fas fa-times"></i> Refuser</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="comment-status-text"><i class="fas fa-tag"></i> <?= ucfirst($commentStatus) ?></span>
                                        <?php endif; ?>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce commentaire ?');">
                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                            <input type="hidden" name="action" value="supprimer_comment">
                                            <button type="submit" class="btn-comment-delete"><i class="fas fa-trash-alt"></i> Supprimer</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;padding-top:15px;border-top:1px solid #f1f5f9;">
                        <span class="status-badge status-<?= strtolower($p['statut'] ?? 'en_attente') ?>">
                            <i class="fas fa-info-circle"></i> <?= ucfirst(str_replace('_', ' ', $p['statut'] ?? 'en_attente')) ?>
                        </span>

                        <div class="action-buttons">
                            <?php $statut = trim(strtolower($p['statut'] ?? 'en_attente')); ?>
                            <?php if ($statut === 'en_attente'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="action" value="approuver_post">
                                    <button type="submit" class="btn-approve"><i class="fas fa-check-circle"></i> Approuver</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="action" value="refuser_post">
                                    <button type="submit" class="btn-refuse"><i class="fas fa-ban"></i> Refuser</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cette publication ?');">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="action" value="supprimer_post">
                                    <button type="submit" class="btn-delete"><i class="fas fa-trash-alt"></i> Supprimer</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>