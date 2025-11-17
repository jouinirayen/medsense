<?php
/**
 * Script de déconnexion
 */

require_once 'config/config.php';
require_once 'config/auth.php';

logout();

header('Location: index.php');
exit();

?>
