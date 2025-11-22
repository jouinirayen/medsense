<?php
include_once 'UtilisateurController.php';


class AdminController {
    private $utilisateurController;

    public function __construct() {
        $this->utilisateurController = new UtilisateurController();
    }

    public function dashboard() {
        if (!$this->isAdmin()) {
            header('Location: ../frontoffice/auth/sign-in.php');
            exit;
        }

        $stats = $this->utilisateurController->getStats();
        $recentUsers = $this->getRecentUsers(5);
        
        return [
            'stats' => $stats,
            'recentUsers' => $recentUsers
        ];
    }

    public function manageUsers($action, $data = null, $id = null) {
        if (!$this->isAdmin()) {
            return array("success" => false, "message" => "Accès non autorisé");
        }

        switch ($action) {
            case 'create':
                $user = new Utilisateur(
                    $data['nom'],
                    $data['prenom'],
                    $data['email'],
                    $data['mot_de_passe'],
                    $data['dateNaissance'],
                    $data['adresse'],
                    $data['role'],
                    $data['statut']
                );
                return $this->utilisateurController->createUser($user);

            case 'update':
                return $this->updateUser($id, $data);

            case 'delete':
                return $this->deleteUser($id);

            case 'get':
                return $this->getUser($id);

            case 'list':
                return $this->getAllUsers();

            default:
                return array("success" => false, "message" => "Action non reconnue");
        }
    }

   
    public function updateUser($id, $data) {
        try {
            $existingUser = $this->utilisateurController->getUserById($id);
            if (!$existingUser) {
                return array("success" => false, "message" => "Utilisateur non trouvé");
            }

            
            if ($id == $_SESSION['user_id'] && isset($data['role']) && $data['role'] !== $_SESSION['user_role']) {
                return array("success" => false, "message" => "Vous ne pouvez pas modifier votre propre rôle");
            }

       
            $user = new Utilisateur();
            $user->setId($id);
            $user->setNom($data['nom'] ?? $existingUser['nom']);
            $user->setPrenom($data['prenom'] ?? $existingUser['prenom']);
            $user->setEmail($data['email'] ?? $existingUser['email']);
            $user->setDateNaissance($data['dateNaissance'] ?? $existingUser['dateNaissance']);
            $user->setAdresse($data['adresse'] ?? $existingUser['adresse']);
            $user->setRole($data['role'] ?? $existingUser['role']);
            $user->setStatut($data['statut'] ?? $existingUser['statut']);

           
            if (!empty($data['mot_de_passe'])) {
                if ($data['mot_de_passe'] !== $data['confirm_mot_de_passe']) {
                    return array("success" => false, "message" => "Les mots de passe ne correspondent pas");
                }
                $user->setMotDePasse(password_hash($data['mot_de_passe'], PASSWORD_DEFAULT));
            }

            return $this->utilisateurController->updateUser($id, $user);

        } catch (Exception $e) {
            return array("success" => false, "message" => "Erreur lors de la modification: " . $e->getMessage());
        }
    }

   
    public function deleteUser($id) {
        try {
            $existingUser = $this->utilisateurController->getUserById($id);
            if (!$existingUser) {
                return array("success" => false, "message" => "Utilisateur non trouvé");
            }

            
            if ($id == $_SESSION['user_id']) {
                return array("success" => false, "message" => "Vous ne pouvez pas supprimer votre propre compte");
            }

            return $this->utilisateurController->deleteUser($id);

        } catch (Exception $e) {
            return array("success" => false, "message" => "Erreur lors de la suppression: " . $e->getMessage());
        }
    }

    
    public function getUser($id) {
        try {
            $user = $this->utilisateurController->getUserById($id);
            if ($user) {
                return array("success" => true, "user" => $user);
            } else {
                return array("success" => false, "message" => "Utilisateur non trouvé");
            }
        } catch (Exception $e) {
            return array("success" => false, "message" => "Erreur lors de la récupération");
        }
    }

   
    public function getAllUsers() {
        try {
            $users = $this->utilisateurController->getAllUsers();
            return array("success" => true, "users" => $users);
        } catch (Exception $e) {
            return array("success" => false, "message" => "Erreur lors de la récupération des utilisateurs");
        }
    }

    private function getRecentUsers($limit = 5) {
        try {
            $pdo = config::getConnexion();
            $req = $pdo->prepare('SELECT * FROM utilisateur ORDER BY date_inscription DESC LIMIT ?');
            $req->execute([$limit]);
            return $req->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    private function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}
?>