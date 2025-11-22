<?php
require_once __DIR__ .'/../models/Utilisateur.php'; 
require_once __DIR__ . '/../config.php';
class AuthController {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function register($userData) {
        try {
            // Validation basique
            if (empty($userData['nom']) || empty($userData['prenom']) || empty($userData['email']) || empty($userData['mot_de_passe'])) {
                return ["success" => false, "message" => "Tous les champs sont obligatoires"];
            }

            // Vérifier si email existe
            $check = $this->pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
            $check->execute([$userData['email']]);
            if ($check->fetch()) {
                return ["success" => false, "message" => "Cet email existe déjà"];
            }

            // Créer l'utilisateur
            $stmt = $this->pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, dateNaissance, adresse) VALUES (?, ?, ?, ?, ?, ?)");
            $hashedPassword = password_hash($userData['mot_de_passe'], PASSWORD_DEFAULT);
            
            $stmt->execute([
                $userData['nom'],
                $userData['prenom'], 
                $userData['email'],
                $hashedPassword,
                $userData['dateNaissance'] ?? null,
                $userData['adresse'] ?? null
            ]);

            // Auto-login après inscription
            return $this->login($userData['email'], $userData['mot_de_passe']);

        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    public function login($email, $password) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                $_SESSION['user_id'] = $user['id_utilisateur'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                return ["success" => true, "message" => "Connexion réussie"];
            }

            return ["success" => false, "message" => "Email ou mot de passe incorrect"];

        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) return null;
        
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }

    public function handleLogout() {
        // Démarrer la session si pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vider toutes les variables de session
        $_SESSION = array();
        
        // Détruire la session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        
        // Redirection
        header("Location: ../../views/frontoffice/home/index.php");
        exit;
    }
}

// 🔥 CORRECTION : Gestion de la déconnexion - DOIT ÊTRE EN DEHORS DE LA CLASSE
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $authController = new AuthController();
    $authController->handleLogout();
}
?>