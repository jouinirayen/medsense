<?php
session_start();
if (!isset($_SESSION['admin'])) exit;

require_once '../../Controller/blogC.php';
$blogC = new blogC();

if (isset($_POST['id'])) {
    $blogC->approuverPost($_POST['id']);
}
header("Location: dashboard.php");
exit;
?>