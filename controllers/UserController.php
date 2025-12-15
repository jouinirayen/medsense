<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

class UserController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function getDoctorsByService($serviceId)
    {
        try {
            $pdo = (new config())->getConnexion();
            $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE role = 'medecin' AND idService = ? AND statut = 'actif'");
            $stmt->execute([$serviceId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die("Erreur lors de la récupération des médecins: " . $e->getMessage());
        }
    }

    public function login($email, $password)
    {
        try {
            $pdo = (new config())->getConnexion();
            $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Check password (support both hash and plain text for legacy/test data)
                if (password_verify($password, $user['mot_de_passe']) || $password === $user['mot_de_passe']) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id_utilisateur'];
                    $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
                    $_SESSION['prenom'] = $user['prenom'];
                    $_SESSION['nom'] = $user['nom'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    return $user;
                }
            }
            return false;
        } catch (PDOException $e) {
            die("Erreur lors de la connexion: " . $e->getMessage());
        }
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        header('Location: ../../projet_unifie/views/frontoffice/home/index.php');
        exit;
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function requireRole($allowedRoles)
    {
        if (!$this->isLoggedIn()) {
            header('Location: ../views/frontoffice/home/index.php');
            exit;
        }

        if (!is_array($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }

        if (!in_array($_SESSION['role'], $allowedRoles)) {
            // Redirect to a default page based on their actual role, or home
            $this->redirectBasedOnRole($_SESSION['role']);
        }
    }

    public function redirectBasedOnRole($role)
    {
        switch ($role) {
            case 'medecin':
                header('Location: /projet_unifie/views/backoffice/dashboard_medecin/afficher_rendezvous_medecin.php');
                break;
            case 'admin':
                header('Location: /projet_unifie/views/backoffice/admin_hub.php');
                break;
            default:
                header('Location: /projet_unifie/views/frontoffice/page-accueil/front.php');
                break;
        }
        exit;
    }

    public function getUserById($id)
    {
        try {
            $pdo = (new config())->getConnexion();
            $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur lors de la récupération de l'utilisateur: " . $e->getMessage());
        }
    }
}
?>