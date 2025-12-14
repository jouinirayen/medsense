
<?php
require_once '../../Controller/commentaireC.php';

$cc = new commentaireC();

if ($_POST['contenu'] && isset($_POST['id'])) {
    $nouveau = trim($_POST['contenu']);
    $id = (int)$_POST['id'];
    $postId = (int)$_POST['post_id'];

    if ($nouveau !== "") {
        $cc->modifierCommentaire($id, $nouveau);
    }
}

header("Location: liste.php#post-" . $postId);
exit;
?>
