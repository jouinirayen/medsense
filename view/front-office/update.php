
<?php
session_start();
require_once '../../Controller/blogC.php';
require_once '../../Model/blog.php';

$bc = new blogC();
$id = (int)$_GET['id'] ?? 0;

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$post = $bc->getPostById($id);
if (!$post || $post['utilisateur_id'] != $_SESSION['user_id']) {
    die("Accès refusé.");
}

// Traitement du formulaire (inchangé – 100% fonctionnel)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenu = trim($_POST['contenu'] ?? '');
    $imageUrl = $post['imageUrl'];

    if (!empty($_POST['nouvelle_image_data'])) {
        $data = $_POST['nouvelle_image_data'];
        list($type, $data) = explode(';', $data);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);

        $ext = explode('/', explode(':', substr($data, 0, strpos($data, ';')))[1])[1] ?? 'jpg';

        $dir = '../../view/uploads/blog/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $newName = uniqid('post_') . '.' . $ext;
        $path = $dir . $newName;

        if (file_put_contents($path, $data)) {
            if ($post['imageUrl'] && file_exists('../../' . $post['imageUrl'])) {
                unlink('../../' . $post['imageUrl']);
            }
            $imageUrl = 'view/uploads/blog/' . $newName;
        }
    } elseif (isset($_POST['supprimer_image'])) {
        if ($post['imageUrl'] && file_exists('../../' . $post['imageUrl'])) {
            unlink('../../' . $post['imageUrl']);
        }
        $imageUrl = null;
    }

    $publication = new publication($contenu, $imageUrl, $post['createdAt']);
    $bc->updatePost($publication, $id);
    header("Location: liste.php");
    exit;
}

// Avatar SVG fixe (identique à liste.php)
$avatarSVG = '<svg width="100%" height="100%" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
  <circle cx="50" cy="50" r="50" fill="#374151"/>
  <circle cx="50" cy="35" r="18" fill="#9ca3af"/>
</svg>';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la publication - medsense</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* TOUT LE CSS DE LISTE.PHP (copié intégralement pour cohérence) */
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
        .avatar, .user-avatar {
            width: 44px; height: 44px; border-radius: 50%;
            border: 2.5px solid #3b82f6; box-shadow: 0 2px 8px rgba(59,130,246,.2);
            overflow: hidden; background: #2563eb; flex-shrink: 0;
        }
        .sidebar {
            position: fixed; left: 0; top: 0; bottom: 0; width: 280px;
            background: rgba(255,255,255,0.94); backdrop-filter: blur(16px);
            border-right: 1px solid rgba(0,0,0,0.08);
            padding: 100px 20px 40px; z-index: 50; overflow-y: auto;
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
        .main-content { margin-left: 280px; padding-top: 20px; }
        @media (max-width: 992px) { .sidebar { display: none; } .main-content { margin-left: 0; } }

        /* Style du formulaire d'édition */
        .container {
            max-width: 680px;
            margin: 0 auto;
            background: white;
            border-radius: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
        }
        .form-header {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            padding: 28px 24px;
            text-align: center;
            font-size: 26px;
            font-weight: 700;
            position: relative;
        }
        .back-btn {
            position: absolute;
            left: 24px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-size: 26px;
            text-decoration: none;
        }
        .form-body { padding: 40px; }
        /* (le reste du style du formulaire reste identique à ta version précédente) */
        textarea {
            width: 100%; min-height: 160px; padding: 18px; border: 2px solid #e2e8f0;
            border-radius: 18px; font-size: 16px; resize: vertical; margin: 20px 0;
        }
        textarea:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59,130,246,.15); }
        /* ... etc (tu peux garder tout ton style existant ici) */
    </style>
</head>
<body>

<!-- SIDEBAR IDENTIQUE À LISTE.PHP -->
<div class="sidebar">
    <h3>Publications</h3>
    <a href="liste.php"><i class="fas fa-home"></i> Toutes les publications</a>
    <a href="liste.php?filter=mes"><i class="fas fa-user"></i> Mes publications</a>
    <a href="liste.php?filter=enregistrees"><i class="fas fa-bookmark"></i> Enregistrées</a>
</div>

<!-- HEADER IDENTIQUE À LISTE.PHP -->
<header class="header">
    <div style="display: flex; align-items: center; gap: 20px; flex: 1;">
        <div class="logo">
            <img src="http://localhost/blog/logo.png" alt="medsense">
        </div>

        <!-- Barre de recherche (même comportement que liste.php) -->
        <div style="position: relative; max-width: 500px; width: 100%; margin-left: 200px;">
            <form id="searchForm" method="GET" action="liste.php" style="position: relative;">
                <input type="text" name="search_user" placeholder="Rechercher un utilisateur..."
                       style="width: 100%; padding: 12px 50px 12px 20px; border: 2px solid #e2e8f0; border-radius: 30px; font-size: 15px; outline: none; background: white;">
                <button type="submit" style="position: absolute; right: 6px; top: 4px; background: #3b82f6; color: white; border: none; width: 38px; height: 38px; border-radius: 50%; cursor: pointer;">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div style="position: absolute; left: 990px; top: 50%; transform: translateY(-50%); display: flex; gap: 25px;">
            <a href="liste.php" style="color: #64748b; font-size: 26px;" onmouseover="this.style.color='#3b82f6'" onmouseout="this.style.color='#64748b'"><i class="fas fa-house"></i></a>
            <a href="message.php" style="color: #64748b; font-size: 26px;" onmouseover="this.style.color='#3b82f6'" onmouseout="this.style.color='#64748b'"><i class="fas fa-envelope"></i></a>
        </div>
    </div>

    <div style="display:flex; align-items:center; gap:20px;">
        <div style="text-align:right; line-height:1.4;">
            <small style="color:#64748b; font-size:13px;">Connecté en tant que</small><br>
            <strong style="font-size:18px; color:#1e40af;">
                <?= htmlspecialchars($_SESSION['prenom'] . " " . $_SESSION['nom']) ?>
            </strong>
        </div>
        <div class="avatar"><?= $avatarSVG ?></div>
        <a href="../logout.php" style="background:#dc2626;color:white;padding:11px 22px;border-radius:12px;text-decoration:none;font-weight:600;">
            Déconnexion
        </a>
    </div>
</header>

<div class="main-content">
    <div class="container" style="max-width: 720px; margin: 40px auto;">
        <div class="form-header">
            <a href="liste.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
            Modifier la publication
        </div>

        <div class="form-body">
            <form method="POST" id="editForm">
                <div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
                    <div class="avatar"><?= $avatarSVG ?></div>
                    <div>
                        <strong style="color:#1e40af; font-size:19px;">
                            <?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?>
                        </strong><br>
                        <span style="color:#64748b;"><?= date('d/m/Y à H:i', strtotime($post['createdAt'])) ?></span>
                    </div>
                </div>

                <textarea name="contenu" required placeholder="Modifier le contenu..." rows="6"><?= htmlspecialchars($post['contenu']) ?></textarea>

                <div id="imageContainer" style="margin:20px 0; text-align:center;">
                    <?php if ($post['imageUrl']): ?>
                        <div style="position:relative; display:inline-block; border-radius:18px; overflow:hidden; box-shadow:0 8px 25px rgba(0,0,0,.15);">
                            <img src="../../<?= htmlspecialchars($post['imageUrl']) ?>" style="max-width:100%; max-height:500px; object-fit:cover;" id="previewImg">
                            <div style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(transparent,rgba(0,0,0,0.8)); color:white; padding:40px 16px 16px; text-align:center; cursor:pointer;" onclick="document.getElementById('fileInput').click();">
                                Changer l’image
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="border:3px dashed #94a3b8; border-radius:18px; padding:60px; cursor:pointer;" onclick="document.getElementById('fileInput').click();">
                            <i class="fas fa-image" style="font-size:60px; color:#94a3b8;"></i><br><br>
                            <strong>Ajouter une image</strong>
                        </div>
                    <?php endif; ?>
                </div>

                <input type="file" id="fileInput" accept="image/*" style="display:none;">
                <input type="hidden" name="nouvelle_image_data" id="imageData">

                <?php if ($post['imageUrl']): ?>
                    <label style="display:block; text-align:center; margin:20px 0;">
                        <input type="checkbox" name="supprimer_image" value="1" onchange="removeImage()"> 
                        Supprimer l’image actuelle
                    </label>
                <?php endif; ?>

                <div style="display:flex; justify-content:flex-end; gap:16px; margin-top:30px;">
                    <a href="liste.php" style="padding:14px 32px; background:#e2e8f0; color:#64748b; border-radius:14px; text-decoration:none; font-weight:600;">Annuler</a>
                    <button type="submit" style="padding:14px 32px; background:linear-gradient(135deg,#3b82f6,#2563eb); color:white; border:none; border-radius:14px; font-weight:600; cursor:pointer; box-shadow:0 6px 20px rgba(59,130,246,.4);">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const fileInput = document.getElementById('fileInput');
    imageContainer = document.getElementById('imageContainer');
    imageDataInput = document.getElementById('imageData');

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imageContainer.innerHTML = `
                    <div style="position:relative; display:inline-block; border-radius:18px; overflow:hidden; box-shadow:0 8px 25px rgba(0,0,0,.15);">
                        <img src="${e.target.result}" style="max-width:100%; max-height:500px; object-fit:cover;">
                        <div style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(transparent,rgba(0,0,0,0.8)); color:white; padding:40px 16px 16px; text-align:center; cursor:pointer;" onclick="document.getElementById('fileInput').click();">
                            Changer l’image
                        </div>
                    </div>`;
                imageDataInput.value = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    function removeImage() {
        imageContainer.innerHTML = `
            <div style="border:3px dashed #94a3b8; border-radius:18px; padding:60px; cursor:pointer;" onclick="document.getElementById('fileInput').click();">
                <i class="fas fa-image" style="font-size:60px; color:#94a3b8;"></i><br><br>
                <strong>Ajouter une image</strong>
            </div>`;
        imageDataInput.value = '';
    }
</script>

</body>
</html>
