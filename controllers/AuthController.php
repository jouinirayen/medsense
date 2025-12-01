<?php

require_once __DIR__ . '/../config.php';          
require_once __DIR__ . '/../models/Utilisateur.php'; 
require_once __DIR__ . '/../models/UtilisateurRepository.php'; 

class AuthController 
{
    private $pdo;                    
    private $userRepository;         

    public function __construct() 
    {
        $this->pdo = config::getConnexion();
        $this->userRepository = new UtilisateurRepository($this->pdo);
    }

    public function register($userData) 
    {
        try {
            
            $errors = $this->validateRegistrationData($userData);
            if (!empty($errors)) {
                return ["success" => false, "message" => "Données invalides", "errors" => $errors];
            }

            if ($this->userRepository->emailExists($userData['email'])) {
                return ["success" => false, "message" => "Cet email existe déjà"];
            }

            $user = new Utilisateur(
                $userData['nom'],
                $userData['prenom'], 
                $userData['email'],
                $userData['mot_de_passe'],
                $userData['dateNaissance'] ?? null, 
                $userData['adresse'] ?? null       
            );


            if ($this->userRepository->save($user)) {
                
                return $this->login($userData['email'], $userData['mot_de_passe']);
            }

            return ["success" => false, "message" => "Erreur lors de l'inscription"];

        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    
    public function login($email, $password) 
    {
        try {
            $user = $this->userRepository->findByEmail($email);
            if (!$user || !$user->verifyMotDePasse($password)) {
                return ["success" => false, "message" => "Email ou mot de passe incorrect"];
            }
            if (!$user->estActif()) {
                return ["success" => false, "message" => "Votre compte est désactivé"];
            }

            $this->startUserSession($user);

            return [
                "success" => true, 
                "message" => "Connexion réussie",
                "user" => $user->toArray() 
            ];

        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

   
    public function logout() 
    {
        $this->destroySession();
        return ["success" => true, "message" => "Déconnexion réussie"];
    }

   
    public function deactivateAccount($userId = null, $password = null) 
    {
        try {
            // Déterminer quel utilisateur désactiver
            if ($userId === null) {
                // Désactivation de son propre compte (utilisateur connecté)
                if (!$this->isLoggedIn()) {
                    return ["success" => false, "message" => "Vous devez être connecté"];
                }
                $user = $this->getCurrentUser();
                
                // Vérification du mot de passe pour sécurité
                if ($password && !$user->verifyMotDePasse($password)) {
                    return ["success" => false, "message" => "Mot de passe incorrect"];
                }
            } else {
                // Désactivation par un administrateur (d'un autre utilisateur)
                $user = $this->userRepository->find($userId);
                if (!$user) {
                    return ["success" => false, "message" => "Utilisateur non trouvé"];
                }
            }

            // Vérifier si l'utilisateur peut être désactivé (pas un admin principal par exemple)
            if ($user->getRole() === 'superadmin') {
                return ["success" => false, "message" => "Impossible de désactiver un super administrateur"];
            }

            // Méthode 1 : Désactivation (soft delete) - recommandé
            // On marque simplement le compte comme inactif
            $user->setActif(false);
            $success = $this->userRepository->save($user);
            
            // Méthode 2 : Suppression définitive (hard delete) - décommenter si nécessaire
            // $success = $this->userRepository->delete($user->getId());

            if ($success) {
                // Si l'utilisateur désactive son propre compte, on le déconnecte
                if ($userId === null || $user->getId() == $_SESSION['user_id']) {
                    $this->destroySession();
                    return ["success" => true, "message" => "Votre compte a été désactivé avec succès"];
                }
                
                return ["success" => true, "message" => "Le compte utilisateur a été désactivé avec succès"];
            }

            return ["success" => false, "message" => "Erreur lors de la désactivation du compte"];

        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

   
    public function getCurrentUser() 
    {
        if (!$this->isLoggedIn()) return null;
        
        return $this->userRepository->find($_SESSION['user_id']);
    }

   
    public function isLoggedIn() 
    {
        return isset($_SESSION['user_id']);
    }

    
    public function hasRole($role) 
    {
        if (!$this->isLoggedIn()) return false;
        
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

  
    private function validateRegistrationData($data) 
    {
        $errors = [];

        if (empty(trim($data['nom']))) {
            $errors['nom'] = 'Le nom est obligatoire';
        } elseif (strlen(trim($data['nom'])) < 2) {
            $errors['nom'] = 'Le nom doit contenir au moins 2 caractères';
        }


        if (empty(trim($data['prenom']))) {
            $errors['prenom'] = 'Le prénom est obligatoire';
        } elseif (strlen(trim($data['prenom'])) < 2) {
            $errors['prenom'] = 'Le prénom doit contenir au moins 2 caractères';
        }

        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalide';
        }

       
        if (empty($data['mot_de_passe']) || strlen($data['mot_de_passe']) < 6) {
            $errors['mot_de_passe'] = 'Le mot de passe doit contenir au moins 6 caractères';
        }

    
        if (!empty($data['dateNaissance'])) {
            $date = DateTime::createFromFormat('Y-m-d', $data['dateNaissance']);
            if (!$date || $date->format('Y-m-d') !== $data['dateNaissance']) {
                $errors['dateNaissance'] = 'Date de naissance invalide (format YYYY-MM-DD attendu)';
            }
        }

        return $errors;
    }

   
    private function startUserSession(Utilisateur $user) 
    {
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_nom'] = $user->getNom();
        $_SESSION['user_prenom'] = $user->getPrenom();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_role'] = $user->getRole();
        $_SESSION['user_photo'] = $user->getPhotoProfil();
        $_SESSION['user_actif'] = $user->estActif();
        $_SESSION['login_time'] = time(); // Timestamp de connexion
    }

    private function destroySession() 
    {
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

       
        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        
        session_destroy();
    }
}


if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $authController = new AuthController();
    $result = $authController->logout();
   
    header("Location: ../../views/frontoffice/home/index.php");
    exit;
}


if (isset($_GET['action']) && $_GET['action'] === 'deactivate') {
    $authController = new AuthController();
    
   
    if (!$authController->isLoggedIn()) {
        header("Location: ../../views/frontoffice/auth/login.php?error=not_logged_in");
        exit;
    }
    
  
    $password = $_POST['password'] ?? null;
    $result = $authController->deactivateAccount(null, $password);
    
   
    if ($result['success']) {
        header("Location: ../../views/frontoffice/home/index.php?message=account_deactivated");
    } else {
        header("Location: ../../views/frontoffice/user/profile.php?error=" . urlencode($result['message']));
    }
    exit;
}
?>