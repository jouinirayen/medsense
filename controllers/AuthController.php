<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Utilisateur.php';

class AuthController {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function register($userData): array {
        try {
            
            $errors = $this->validateRegistrationData($userData);
            if (!empty($errors)) {
                return ["success" => false, "message" => "Données invalides", "errors" => $errors];
            }

            if ($this->emailExists($userData['email'])) {
                return ["success" => false, "message" => "Cet email existe déjà"];
            }

            
            $user = new Utilisateur(
                $userData['nom'],
                $userData['prenom'],
                $userData['email'],
                $userData['mot_de_passe'], 
                $userData['dateNaissance'] ?? '',
                $userData['adresse'] ?? '',
                $userData['role'] ?? 'patient',
                $userData['statut'] ?? 'en_attente', 
                $userData['diplome_path'] ?? null,
                null, 
                null,
                null, 
                null, 
                null, 
                null, 
                null, 
                null,
                null, 
                null,
                null, 
                0,    
                0,    
                $userData['langues'] ?? null,
                null,
                $userData['experience'] ?? null,
                null, 
                null  
            );

         
            if (($userData['role'] ?? 'patient') === 'medecin') {
                $user->setStatut('en_attente');
            }

            error_log("DEBUG - Mot de passe après création utilisateur:");
            error_log("  - Original: " . $userData['mot_de_passe']);
            error_log("  - Hashé: " . $user->getMotDePasse());
            error_log("  - Longueur hash: " . strlen($user->getMotDePasse()));

         
            if ($this->saveUser($user)) {
             
                $freshUser = $this->findUserByEmail($userData['email']);
                
                if (!$freshUser) {
                    return ["success" => false, "message" => "Compte créé mais impossible de récupérer les données"];
                }
                
                error_log("DEBUG - Utilisateur récupéré de la base:");
                error_log("  - Email: " . $freshUser->getEmail());
                error_log("  - Hash en base: " . substr($freshUser->getMotDePasse(), 0, 20) . "...");
                
                
                return $this->performAutoLogin($freshUser, $userData['mot_de_passe']);
            }

            return ["success" => false, "message" => "Erreur lors de l'inscription"];
        } catch (Exception $e) {
            error_log("ERREUR register: " . $e->getMessage());
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    private function performAutoLogin(Utilisateur $user, $plainPassword): array {
        try {
            error_log("DEBUG - Auto-login pour: " . $user->getEmail());
            
            if (!password_verify($plainPassword, $user->getMotDePasse())) {
                error_log("DEBUG - password_verify ÉCHEC!");
                
                return [
                    "success" => true,
                    "message" => "Compte créé avec succès ! Veuillez vous connecter avec vos identifiants.",
                    "user_created" => true,
                    "email" => $user->getEmail()
                ];
            }
            
            error_log("DEBUG - password_verify SUCCÈS!");
            
          
            if ($user->getStatut() !== 'actif') {
                if ($user->getStatut() === 'en_attente') {
                    return [
                        "success" => true,
                        "message" => "Compte créé avec succès ! Votre compte médecin est en attente de validation par l'administrateur.",
                        "user_created" => true,
                        "email" => $user->getEmail()
                    ];
                }
                return [
                    "success" => true,
                    "message" => "Compte créé mais désactivé. Contactez l'administrateur.",
                    "user_created" => true,
                    "email" => $user->getEmail()
                ];
            }

            $this->startUserSession($user);

            return [
                "success" => true,
                "message" => "Inscription et connexion réussies !",
                "user" => [
                    'id_utilisateur' => $user->getId(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole(),
                    'statut' => $user->getStatut()
                ]
            ];
        } catch (Exception $e) {
            error_log("ERREUR performAutoLogin: " . $e->getMessage());
            return [
                "success" => true,
                "message" => "Compte créé avec succès ! Veuillez vous connecter.",
                "user_created" => true,
                "email" => $user->getEmail()
            ];
        }
    }

    public function login($email, $password): array {
        try {
            error_log("DEBUG - Tentative de login pour: " . $email);
            
            $user = $this->findUserByEmail($email);
            
            if (!$user) {
                error_log("DEBUG - Utilisateur non trouvé: " . $email);
                return ["success" => false, "message" => "Email ou mot de passe incorrect"];
            }
            
            error_log("DEBUG - Utilisateur trouvé, vérification mot de passe...");
            
        
            if (!password_verify($password, $user->getMotDePasse())) {
                error_log("DEBUG - password_verify ÉCHEC pour: " . $email);
                return ["success" => false, "message" => "Email ou mot de passe incorrect"];
            }
            
            error_log("DEBUG - password_verify SUCCÈS pour: " . $email);
            
        
            if ($user->getStatut() !== 'actif') {
                if ($user->getStatut() === 'en_attente') {
                    return ["success" => false, "message" => "Votre compte est en attente d'activation par l'administrateur"];
                }
                return ["success" => false, "message" => "Votre compte est désactivé"];
            }

          
            $this->startUserSession($user);

            return [
                "success" => true,
                "message" => "Connexion réussie",
                "user" => [
                    'id_utilisateur' => $user->getId(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole(),
                    'statut' => $user->getStatut()
                ]
            ];
        } catch (Exception $e) {
            error_log("ERREUR login: " . $e->getMessage());
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    public function logout(): array {
        $this->destroySession();
        return ["success" => true, "message" => "Déconnexion réussie"];
    }

    public function deactivateAccount($userId = null, $password = null): array {
        try {
            if ($userId === null) {
                if (!$this->isLoggedIn()) {
                    return ["success" => false, "message" => "Vous devez être connecté"];
                }
                
                $user = $this->findUserById($_SESSION['user_id']);
                
                if (!$user) {
                    return ["success" => false, "message" => "Utilisateur non trouvé"];
                }
                
                if ($password && !password_verify($password, $user->getMotDePasse())) {
                    return ["success" => false, "message" => "Mot de passe incorrect"];
                }
                
                $user->setStatut('inactif');
                $success = $this->updateUserStatus($user->getId(), 'inactif');
                
                if ($success) {
                    $this->destroySession();
                    return ["success" => true, "message" => "Votre compte a été désactivé avec succès"];
                }
            } else {
                $user = $this->findUserById($userId);
                if (!$user) {
                    return ["success" => false, "message" => "Utilisateur non trouvé"];
                }
                
                $user->setStatut('inactif');
                $success = $this->updateUserStatus($userId, 'inactif');
                
                if ($success) {
                    return ["success" => true, "message" => "Le compte utilisateur a été désactivé avec succès"];
                }
            }

            return ["success" => false, "message" => "Erreur lors de la désactivation du compte"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    public function getCurrentUser(): ?Utilisateur {
        if (!$this->isLoggedIn()) return null;
        return $this->findUserById($_SESSION['user_id']);
    }

    public function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }

    public function hasRole($role): bool {
        if (!$this->isLoggedIn()) return false;
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    private function emailExists($email, $excludeId = null): bool {
        try {
            if ($excludeId) {
                $query = "SELECT COUNT(*) FROM utilisateur WHERE email = ? AND id_utilisateur != ?";
                $stmt = $this->pdo->prepare($query);
                $stmt->execute([$email, $excludeId]);
            } else {
                $query = "SELECT COUNT(*) FROM utilisateur WHERE email = ?";
                $stmt = $this->pdo->prepare($query);
                $stmt->execute([$email]);
            }
            
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Erreur emailExists: " . $e->getMessage());
            return false;
        }
    }

    private function saveUser(Utilisateur $user): bool {
        try {
            $query = "INSERT INTO utilisateur (
                nom, prenom, email, mot_de_passe, dateNaissance, adresse, 
                role, statut, diplome_path, bio, idService, heure1_debut, 
                heure1_fin, heure2_debut, heure2_fin, heure3_debut, 
                heure3_fin, heure4_debut, heure4_fin, image, note_globale, 
                nb_avis, langues, prix_consultation, experience, username, 
                specialite, date_inscription, reset_token, reset_token_expires, 
                historique_connexions, diplome_statut, diplome_commentaire, 
                diplome_date_verification, derniere_connexion
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";
            
            $stmt = $this->pdo->prepare($query);
            
            $result = $stmt->execute([
                $user->getNom(),
                $user->getPrenom(),
                $user->getEmail(),
                $user->getMotDePasse(),
                $user->getDateNaissance(),
                $user->getAdresse(),
                $user->getRole(),
                $user->getStatut(),
                $user->getDiplomePath(),
                $user->getBio(),
                $user->getIdService(),
                $user->getHeure1Debut(),
                $user->getHeure1Fin(),
                $user->getHeure2Debut(),
                $user->getHeure2Fin(),
                $user->getHeure3Debut(),
                $user->getHeure3Fin(),
                $user->getHeure4Debut(),
                $user->getHeure4Fin(),
                $user->getImage(),
                $user->getNoteGlobale(),
                $user->getNbAvis(),
                $user->getLangues(),
                $user->getPrixConsultation(),
                $user->getExperience(),
                $user->getUsername(),
                $user->getSpecialite(),
                $user->getDateInscription(),
                $user->getResetToken(),
                $user->getResetTokenExpires(),
                $user->getHistoriqueConnexions(),
                $user->getDiplomeStatut(),
                $user->getDiplomeCommentaire(),
                $user->getDiplomeDateVerification(),
                $user->getDerniereConnexion()
            ]);
            
            if ($result) {
                error_log("DEBUG - Utilisateur inséré avec succès, ID: " . $this->pdo->lastInsertId());
            } else {
                error_log("DEBUG - Échec insertion utilisateur");
                $errorInfo = $stmt->errorInfo();
                error_log("  - Erreur SQL: " . print_r($errorInfo, true));
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("ERREUR saveUser: " . $e->getMessage());
            return false;
        }
    }

    private function findUserByEmail($email): ?Utilisateur {
        try {
            $query = "SELECT * FROM utilisateur WHERE email = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) {
                error_log("DEBUG - findUserByEmail: aucun utilisateur trouvé pour " . $email);
                return null;
            }
            
            error_log("DEBUG - findUserByEmail: utilisateur trouvé pour " . $email);
            return $this->rowToUser($row);
        } catch (Exception $e) {
            error_log("ERREUR findUserByEmail: " . $e->getMessage());
            return null;
        }
    }

    private function findUserById($id): ?Utilisateur {
        try {
            $query = "SELECT * FROM utilisateur WHERE id_utilisateur = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) return null;
            
            return $this->rowToUser($row);
        } catch (Exception $e) {
            error_log("Erreur findUserById: " . $e->getMessage());
            return null;
        }
    }

    private function rowToUser($row): Utilisateur {
  
        $user = new Utilisateur(
            $row['nom'],
            $row['prenom'],
            $row['email'],
            '',
            $row['dateNaissance'] ?? '',
            $row['adresse'] ?? '',
            $row['role'] ?? 'patient',
            $row['statut'] ?? 'en_attente',
            $row['diplome_path'] ?? null,
            $row['bio'] ?? null,
            $row['idService'] ?? null,
            $row['heure1_debut'] ?? null,
            $row['heure1_fin'] ?? null,
            $row['heure2_debut'] ?? null,
            $row['heure2_fin'] ?? null,
            $row['heure3_debut'] ?? null,
            $row['heure3_fin'] ?? null,
            $row['heure4_debut'] ?? null,
            $row['heure4_fin'] ?? null,
            $row['image'] ?? null,
            $row['note_globale'] ?? 0,
            $row['nb_avis'] ?? 0,
            $row['langues'] ?? null,
            $row['prix_consultation'] ?? null,
            $row['experience'] ?? null,
            $row['username'] ?? null,
            $row['specialite'] ?? null
        );
        
     
        $user->setId($row['id_utilisateur']);
    
        $user->setMotDePasse($row['mot_de_passe'], true);
        
        $user->setDateInscription($row['date_inscription'] ?? date('Y-m-d H:i:s'));
        $user->setResetToken($row['reset_token'] ?? null);
        $user->setResetTokenExpires($row['reset_token_expires'] ?? null);
        $user->setHistoriqueConnexions($row['historique_connexions'] ?? null);
        $user->setDiplomeStatut($row['diplome_statut'] ?? 'en attente');
        $user->setDiplomeCommentaire($row['diplome_commentaire'] ?? null);
        $user->setDiplomeDateVerification($row['diplome_date_verification'] ?? null);
        $user->setDerniereConnexion($row['derniere_connexion'] ?? null);
        
        return $user;
    }
    
    private function updateUserStatus($userId, $status): bool {
        try {
            $query = "UPDATE utilisateur SET statut = ? WHERE id_utilisateur = ?";
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([$status, $userId]);
        } catch (Exception $e) {
            error_log("Erreur updateUserStatus: " . $e->getMessage());
            return false;
        }
    }

    private function validateRegistrationData($data): array {
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

    private function startUserSession(Utilisateur $user): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_nom'] = $user->getNom();
        $_SESSION['user_prenom'] = $user->getPrenom();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_role'] = $user->getRole();
        $_SESSION['login_time'] = time();
        
        error_log("DEBUG - Session démarrée pour: " . $user->getEmail());
    }

    private function destroySession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
        
        error_log("DEBUG - Session détruite");
    }

    public function exportUsersPDF($filters = []) {
      
        return false;
    }
}