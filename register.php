<?php
/**
 * Page d'inscription
 */

require_once 'config/config.php';
require_once 'config/db.php';
require_once 'config/auth.php';

// Si déjà connecté, rediriger vers l'accueil
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$pageTitle = "Inscription";
$errors = array();

// Traiter le formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $password_confirm = isset($_POST['password_confirm']) ? trim($_POST['password_confirm']) : '';

    // Validation
    if (empty($username)) {
        $errors[] = "Le nom d'utilisateur est requis.";
    }
    if (strlen($username) < 3) {
        $errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères.";
    }
    if (strlen($username) > 50) {
        $errors[] = "Le nom d'utilisateur ne doit pas dépasser 50 caractères.";
    }

    if (empty($email)) {
        $errors[] = "L'email est requis.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    }

    if (empty($password)) {
        $errors[] = "Le mot de passe est requis.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    }
    if ($password !== $password_confirm) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    // Vérifier l'unicité du nom d'utilisateur et email
    if (empty($errors)) {
        try {
            $db = Database::getInstance();
            
            $sqlCheck = "SELECT COUNT(*) as count FROM user WHERE username = ? OR email = ?";
            $check = $db->fetch($sqlCheck, array($username, $email), 'ss');
            
            if ($check && $check['count'] > 0) {
                $errors[] = "Ce nom d'utilisateur ou email existe déjà.";
            }
        } catch (Exception $e) {
            $errors[] = "Erreur lors de la vérification: " . $e->getMessage();
        }
    }

    // Créer l'utilisateur
    if (empty($errors)) {
        try {
            $db = Database::getInstance();
            
            $hashedPassword = hash('sha256', $password);
            $sql = "INSERT INTO user (username, email, password) VALUES (?, ?, ?)";
            $db->execute($sql, array($username, $email, $hashedPassword), 'sss');

            $_SESSION['success_message'] = "Inscription réussie! Vous pouvez maintenant vous connecter.";
            header('Location: login.php');
            exit();
        } catch (Exception $e) {
            $errors[] = "Erreur lors de l'inscription: " . $e->getMessage();
        }
    }
}

include 'config/header.php';
?>

<h2>Inscription</h2>

<?php if (!empty($errors)): ?>
    <div class="message error">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="message success">
        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<form method="POST">
    <div class="form-group">
        <label for="username">Nom d'utilisateur *</label>
        <input type="text" id="username" name="username" required 
               value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
               minlength="3" maxlength="50">
        <small>3 à 50 caractères</small>
    </div>

    <div class="form-group">
        <label for="email">Email *</label>
        <input type="email" id="email" name="email" required 
               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
               maxlength="100">
    </div>

    <div class="form-group">
        <label for="password">Mot de passe *</label>
        <input type="password" id="password" name="password" required 
               minlength="6">
        <small>Au moins 6 caractères</small>
    </div>

    <div class="form-group">
        <label for="password_confirm">Confirmer le mot de passe *</label>
        <input type="password" id="password_confirm" name="password_confirm" required 
               minlength="6">
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-success">S'inscrire</button>
        <a href="index.php" class="btn">Annuler</a>
    </div>

    <p style="margin-top: 1.5rem; text-align: center;">
        Vous avez déjà un compte ? <a href="login.php">Se connecter</a>
    </p>
</form>

<?php
include 'config/footer.php';
?>
