<?php
session_start();
require_once '../../Controllers/commentaireC.php';
require_once '../../Models/commentaire.php';

// === Vérification de connexion ===
if (!isset($_SESSION['user_id'])) {
    // Si l'utilisateur n'est pas connecté → on le renvoie vers la liste
    header('Location: liste.php');
    exit;
}

$blog_id = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenu = trim($_POST['contenu'] ?? '');
    $blog_id = (int)($_POST['blog_id'] ?? 0);

    // On ajoute le commentaire seulement si le contenu n’est pas vide et qu’on a un blog_id valide
    if ($contenu !== '' && $blog_id > 0) {
        $commentaire = new commentaire($contenu, $blog_id, $_SESSION['user_id']);
        $cc = new commentaireC();
        $cc->ajouterCommentaire($commentaire);
    }
}

// Redirection vers le post concerné (avec ancre) + petit timestamp pour forcer le refresh
header('Location: liste.php?t=' . time() . '#post-' . $blog_id);
exit;
?>