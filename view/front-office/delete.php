
<?php
require_once '../../Controller/blogC.php';

$pc = new blogC();

// Supprimer si un id est passé dans l'URL
$pc->deletePost($_GET['id']);

header('Location: liste.php'); 

?>
