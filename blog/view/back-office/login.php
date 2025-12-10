<?php
session_start();
if ($_POST && $_POST['password'] === "admin123") {  // Change ce mot de passe plus tard !
    $_SESSION['admin'] = true;
    header("Location: dashboard.php");
    exit;
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Connexion Admin</title>
    <style>
        body {font-family: system-ui; background: #1e40af; color: white; display: flex; justify-content: center; align-items: center; height: 100vh; margin:0;}
        form {background: rgba(255,255,255,0.1); padding: 40px; border-radius: 16px; backdrop-filter: blur(10px); box-shadow: 0 10px 30px rgba(0,0,0,0.3);}
        input, button {width: 100%; padding: 12px; margin: 10px 0; border: none; border-radius: 8px;}
        button {background: #3b82f6; color: white; font-weight: bold; cursor: pointer;}
    </style>
</head>
<body>
    <form method="POST">
        <h2>Connexion Admin</h2>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
    </form>
</body>
</html>