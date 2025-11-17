<?php
/**
 * Page de connexion
 */

require_once 'config/config.php';
require_once 'config/db.php';
require_once 'config/auth.php';

// Si déjà connecté, rediriger vers l'accueil
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$pageTitle = "Connexion";
$errors = array();

// Traiter le formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($username)) {
        $errors[] = "Le nom d'utilisateur est requis.";
    }
    if (empty($password)) {
        $errors[] = "Le mot de passe est requis.";
    }

    if (empty($errors)) {
        try {
            $db = Database::getInstance();
            
            // Rechercher l'utilisateur
            $sql = "SELECT * FROM user WHERE username = ?";
            $user = $db->fetch($sql, array($username), 's');

            if ($user) {
                // Vérifier le mot de passe
                if (hash('sha256', $password) === $user['password']) {
                    // Connexion réussie
                    setUser($user['id'], $user['username'], $user['email'], $user['is_admin']);
                    header('Location: index.php');
                    exit();
                } else {
                    $errors[] = "Nom d'utilisateur ou mot de passe incorrect.";
                }
            } else {
                $errors[] = "Nom d'utilisateur ou mot de passe incorrect.";
            }
        } catch (Exception $e) {
            $errors[] = "Erreur lors de la connexion: " . $e->getMessage();
        }
    }
}

include 'config/header.php';
?>

<h2>Connexion</h2>

<?php if (!empty($errors)): ?>
    <div class="message error">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST">
    <div class="form-group">
        <label for="username">Nom d'utilisateur *</label>
        <input type="text" id="username" name="username" required 
               value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
    </div>

    <div class="form-group">
        <label for="password">Mot de passe *</label>
        <input type="password" id="password" name="password" required>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-success">Se Connecter</button>
        <a href="index.php" class="btn">Annuler</a>
    </div>

    <p style="margin-top: 1.5rem; text-align: center;">
        Pas encore de compte ? <a href="register.php">S'inscrire</a>
    </p>

    <div class="message info" style="margin-top: 1.5rem;">
        <strong>Comptes de test :</strong><br>
        Admin : admin / admin123<br>
        User : user1 / user123
    </div>
</form>

<?php
include 'config/footer.php';
?>
