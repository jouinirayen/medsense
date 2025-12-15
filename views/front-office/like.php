
<?php
session_start();
require_once '../../Controllers/likeC.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: liste.php');
    exit;
}

if (!isset($_GET['post_id'])) {
    header('Location: liste.php');
    exit;
}

$post_id = (int)$_GET['post_id'];
$user_id = $_SESSION['user_id'];

$likeC = new likeC();
$likeC->toggleLike($user_id, $post_id);


header('Location: liste.php#post-' . $post_id);
exit;
?>
