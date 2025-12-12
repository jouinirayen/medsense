<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die('Non connecté');
}

require_once '../../controller/blogC.php';
require_once '../../model/blog.php';

// ================= CHEMINS SÛRS =================
$uploadDirPhysical = __DIR__ . '/../../view/uploads/';  // Chemin ABSOLU sur le disque
$uploadDirInDB     = 'uploads/';                        // Ce qu’on enregistre en base (parfait comme ça)

// Créer le dossier s’il n’existe pas
if (!is_dir($uploadDirPhysical)) {
    mkdir($uploadDirPhysical, 0755, true);
}

// ================= CONTENU =================
$contenu = trim($_POST['contenu'] ?? '');
if ($contenu === '') {
    echo 'error_contenu_vide';
    exit;
}

// ================= UPLOAD IMAGE (1 seule) =================
$imageUrl = null;
$extensionsAutorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
$tailleMax = 8 * 1024 * 1024; // 8 Mo max (change si tu veux)

if (!empty($_FILES['images']['name'][0])) {
    foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
        // Vérification erreur upload
        if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        // Vérification taille
        if ($_FILES['images']['size'][$i] > $tailleMax) {
            continue;
        }

        $nomOriginal = $_FILES['images']['name'][$i];
        $ext = strtolower(pathinfo($nomOriginal, PATHINFO_EXTENSION));

        if (!in_array($ext, $extensionsAutorisees)) {
            continue;
        }

        // Nom unique + sécurisé
        $nouveauNom = 'post_' . uniqid('', true) . '.' . $ext;
        $destination = $uploadDirPhysical . $nouveauNom;

        if (move_uploaded_file($tmpName, $destination)) {
            $imageUrl = $uploadDirInDB . $nouveauNom; // → "uploads/post_66f1a2b3c4d5e.jpg"
            break; // on garde uniquement la première image valide
        }
    }
}

// ================= INSERTION EN BASE =================
try {
    $blogC = new blogC();

    $post = new publication(
        $contenu,
        $imageUrl,                    // Parfaitement compatible avec le dashboard
        date('Y-m-d H:i:s'),
        $_SESSION['user_id']
    );

    $blogC->ajouterPost($post);
    echo 'success';

} catch (Exception $e) {
    error_log('Erreur ajout post : ' . $e->getMessage());
    echo 'error';
}

exit;