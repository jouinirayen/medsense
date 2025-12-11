<?php
// views/blog/supprimerCommentaire.php
require_once '../../Controller/commentaireC.php';

if (isset($_GET['id'])) {
    $cc = new commentaireC();
    $cc->supprimerCommentaire($_GET['id']);
}

$postId = $_GET['post'] ?? 1;
header("Location: liste.php");
exit;
?>