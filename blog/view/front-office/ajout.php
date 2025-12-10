<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die('Non connecté');
}

require_once '../../controller/blogC.php';
require_once '../../model/blog.php';

// Dossier uploads
$uploadDir = '../../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Récupération du contenu
$contenu = trim($_POST['contenu'] ?? '');

// Gestion des images (name="images[]" dans ton formulaire)
$imageUrl = null;
if (!empty($_FILES['images']['name'][0])) {
    foreach ($_FILES['images']['name'] as $i => $name) {
        if ($_FILES['images']['error'][$i] === 0) {
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'])) {
                $nom = 'post_' . uniqid() . '.' . $ext;
                $dest = $uploadDir . $nom;
                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $dest)) {
                    $imageUrl = 'uploads/' . $nom;
                    break; // on garde la première image
                }
            }
        }
    }
}

// Insertion en base
try {
    $post = new publication($contenu, $imageUrl, date('Y-m-d H:i:s'), $_SESSION['user_id']);
    $blogC = new blogC();
    $blogC->ajouterPost($post);

    // CETTE LIGNE EST LA SEULE CHOSE IMPORTANTE
    echo 'success';
    exit;

} catch (Exception $e) {
    error_log("Erreur ajout post : " . $e->getMessage());
    echo 'error';
    exit;
}