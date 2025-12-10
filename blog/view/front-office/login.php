<?php
session_start();

try {
    $pdo = new PDO('mysql:host=localhost;dbname=projet2025;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp   = $_POST['mdp'] ?? '';

    if ($email === '' || $mdp === '') {
        $erreur = "Veuillez remplir tous les champs";
    } else {
        // Recherche de l'utilisateur
        $req = $pdo->prepare("SELECT id, prenom, nom, email, motdepasse FROM utilisateur WHERE email = ?");
        $req->execute([$email]);
        $user = $req->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $erreur = "Aucun compte avec cet email";
        } elseif ($mdp === $user['motdepasse']) {  
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['prenom']  = $user['prenom'];
            $_SESSION['nom']     = $user['nom'] ?? '';
            $_SESSION['email']   = $user['email'];

            header("Location: liste.php");
            exit;
        } else {
            $erreur = "Mot de passe incorrect";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Connexion • MedSense</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);height:100vh;display:flex;align-items:center;justify-content:center;}
        .card{background:white;padding:60px;border-radius:24px;box-shadow:0 20px 50px rgba(0,0,0,0.15);width:100%;max-width:420px;text-align:center;}
        .logo{width:80px;height:80px;background:#5a67d8;color:white;font-size:36px;font-weight:bold;border-radius:50%;margin:0 auto 20px;display:flex;align-items:center;justify-content:center;}
        input{width:100%;padding:18px 20px;margin:12px 0;font-size:17px;border:2.5px solid #e2e8f0;border-radius:16px;background:#f8fafc;}
        input:focus{outline:none;border-color:#5a67d8;background:white;box-shadow:0 0 0 5px rgba(90,103,216,.15);}
        button{width:100%;padding:18px;background:linear-gradient(135deg,#5a67d8,#6b46c1);color:white;border:none;border-radius:16px;font-size:18px;font-weight:600;cursor:pointer;margin-top:10px;}
        button:hover{transform:translateY(-4px);box-shadow:0 15px 30px rgba(90,103,216,.3);}
    </style>
</head>
<body>

<div class="card">
    <div class="logo">M</div>
    <h1 style="margin-bottom:8px;">MedSense</h1>
    <p>Connecte-toi avec ton email et le mot de passe <strong>123456</strong></p>

    <form method="post">
        <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($_POST['email']??'') ?>">
        <input type="password" name="mdp" placeholder="Mot de passe (123456)" required>
        <button type="submit">Se connecter</button>
    </form>
</div>

<?php if ($erreur): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Erreur',
    text: '<?= addslashes($erreur) ?>',
    confirmButtonColor: '#5a67d8'
});
</script>
<?php endif; ?>

</body>
</html>