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
                $userData['statut'] ?? 'actif',
                $userData['diplome_path'] ?? null
            );

            
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
            error_log("  - Mot de passe fourni: " . $plainPassword);
            error_log("  - Hash stocké: " . substr($user->getMotDePasse(), 0, 20) . "...");
            
            
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
            error_log("  - Hash stocké: " . substr($user->getMotDePasse(), 0, 20) . "...");
            
           
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
            $query = "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, dateNaissance, adresse, role, statut, diplome_path) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($query);
            
            $result = $stmt->execute([
                $user->getNom(),
                $user->getPrenom(),
                $user->getEmail(),
                $user->getMotDePasse(), // Déjà hashé par le modèle
                $user->getDateNaissance(),
                $user->getAdresse(),
                $user->getRole(),
                $user->getStatut(),
                $user->getDiplomePath()
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

    /**
     * Trouver un utilisateur par email
     */
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

    /**
     * Trouver un utilisateur par ID
     */
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

    /**
     * Convertir une ligne DB en objet Utilisateur
     */
   /**
 * Convertir une ligne DB en objet Utilisateur
 */
private function rowToUser($row): Utilisateur {
    // Créer l'utilisateur avec le hash de la base
    $user = new Utilisateur(
        $row['nom'],
        $row['prenom'],
        $row['email'],
        $row['mot_de_passe'], // Hash de la base
        $row['dateNaissance'] ?? '',
        $row['adresse'] ?? '',
        $row['role'] ?? 'patient',
        $row['statut'] ?? 'actif',
        $row['diplome_path'] ?? null,
        $row['specialite'] ?? null
    );
    
    $user->setId($row['id_utilisateur'])
         ->setDateInscription($row['date_inscription'])
         ->setPhotoProfil($row['photo_profil'] ?? null);
    
    // Ces champs n'existent plus dans votre modèle, commentez-les
    // $user->setResetToken($row['reset_token'] ?? null)
    //      ->setResetTokenExpires($row['reset_token_expires'] ?? null);
    
    // Ajoutez les nouveaux champs si présents dans la base
    if (isset($row['diplome_statut'])) {
        $user->setDiplomeStatut($row['diplome_statut']);
    }
    
    if (isset($row['diplome_commentaire'])) {
        $user->setDiplomeCommentaire($row['diplome_commentaire']);
    }
    
    if (isset($row['diplome_date_verification'])) {
        $user->setDiplomeDateVerification($row['diplome_date_verification']);
    }
    
    if (isset($row['specialite'])) {
        $user->setSpecialite($row['specialite']);
    }
    
    if (isset($row['derniere_connexion'])) {
        $user->setDerniereConnexion($row['derniere_connexion']);
    }
    
    return $user;
}
    /**
     * Mettre à jour le statut d'un utilisateur
     */
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

    /**
     * Valider les données d'inscription
     */
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

    /**
     * Démarrer la session utilisateur
     */
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

    /**
     * Détruire la session
     */
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
    // Récupérer les utilisateurs avec les filtres
    $usersResult = $this->manageUsers('list');
    if (!$usersResult['success']) {
        return false;
    }
    $allUsers = $usersResult['users'];

    // Appliquer les filtres (similaire à admin-users.php)
    $users = $allUsers;
    if (isset($filters['search']) || isset($filters['role']) || isset($filters['statut'])) {
        $search = $filters['search'] ?? '';
        $role_filter = $filters['role'] ?? '';
        $statut_filter = $filters['statut'] ?? '';

        $users = array_filter($allUsers, function($user) use ($search, $role_filter, $statut_filter) {
            $match_search = true;
            $match_role = true;
            $match_statut = true;
            
            // Filtre de recherche
            if ($search) {
                $search_term = strtolower(trim($search));
                $nom = strtolower($user['nom'] ?? '');
                $prenom = strtolower($user['prenom'] ?? '');
                $email = strtolower($user['email'] ?? '');
                
                $match_search = strpos($nom, $search_term) !== false ||
                               strpos($prenom, $search_term) !== false ||
                               strpos($email, $search_term) !== false;
            }
            
            // Filtre par rôle
            if ($role_filter) {
                $match_role = ($user['role'] ?? '') === $role_filter;
            }
            
            // Filtre par statut
            if ($statut_filter) {
                $match_statut = ($user['statut'] ?? '') === $statut_filter;
            }
            
            return $match_search && $match_role && $match_statut;
        });
        
        // Réindexer le tableau après filtrage
        $users = array_values($users);
    }

    // Inclure TCPDF
    require_once(ABSPATH . 'vendor/tecnickcom/tcpdf/tcpdf.php');
    
    // Créer un nouveau document PDF
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Définir les informations du document
    $pdf->SetCreator('Medsense Medical');
    $pdf->SetAuthor('Medsense Medical');
    $pdf->SetTitle('Liste des Utilisateurs');
    $pdf->SetSubject('Export des utilisateurs');
    
    // Supprimer les en-têtes et pieds de page par défaut
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Ajouter une page
    $pdf->AddPage();
    
    // Définir le contenu du PDF
    $html = '<h1>Liste des Utilisateurs</h1>';
    $html .= '<p>Date d\'export : ' . date('d/m/Y H:i:s') . '</p>';
    
    // Ajouter les informations de filtrage si présentes
    if (isset($filters['search']) && $filters['search']) {
        $html .= '<p>Recherche : ' . htmlspecialchars($filters['search']) . '</p>';
    }
    if (isset($filters['role']) && $filters['role']) {
        $html .= '<p>Rôle : ' . htmlspecialchars($filters['role']) . '</p>';
    }
    if (isset($filters['statut']) && $filters['statut']) {
        $html .= '<p>Statut : ' . htmlspecialchars($filters['statut']) . '</p>';
    }
    
    // Tableau des utilisateurs
    $html .= '<table border="1" cellpadding="5" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Date d\'inscription</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($users as $user) {
        $html .= '<tr>
                    <td>' . $user['id_utilisateur'] . '</td>
                    <td>' . htmlspecialchars($user['nom']) . '</td>
                    <td>' . htmlspecialchars($user['prenom']) . '</td>
                    <td>' . htmlspecialchars($user['email']) . '</td>
                    <td>' . htmlspecialchars($user['role']) . '</td>
                    <td>' . htmlspecialchars($user['statut']) . '</td>
                    <td>' . date('d/m/Y', strtotime($user['date_inscription'])) . '</td>
                </tr>';
    }
    
    $html .= '</tbody></table>';
    
    // Écrire le contenu HTML
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Générer le PDF et le télécharger
    $pdf->Output('utilisateurs_export_' . date('Y-m-d_H-i-s') . '.pdf', 'D');
    return true;
}
}
?>