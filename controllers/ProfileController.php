<?php
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../config.php';

class ProfileController {
    private $pdo;
    private $uploadDir;
    private $webUploadPath;

    public function __construct() {
        $this->pdo = config::getConnexion();
        
         $this->uploadDir = __DIR__ . '/../../uploads/profils/';
        $this->webUploadPath = '/uploads/profils/';
        $this->ensureUploadDirExists();  
    }

    
    private function ensureUploadDirExists() {  // NOM CORRECT
        // Créer le dossier s'il n'existe pas
        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                error_log("Erreur: Impossible de créer le dossier " . $this->uploadDir);
                throw new Exception("Impossible de créer le dossier de stockage des photos");
            }
            error_log("Dossier créé: " . $this->uploadDir);
        } else {
            error_log("Dossier existe déjà: " . $this->uploadDir);
        }
        
        // Créer un fichier index.html pour la sécurité
        $indexFile = $this->uploadDir . 'index.html';
        if (!file_exists($indexFile)) {
            file_put_contents($indexFile, '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><h1>Forbidden</h1><p>You don\'t have permission to access this resource.</p></body></html>');
        }
        
        // .htaccess pour Apache (optionnel sur XAMPP)
        $htaccessPath = $this->uploadDir . '.htaccess';
        if (!file_exists($htaccessPath)) {
            $htaccessContent = "Order deny,allow\nDeny from all\n<Files ~ \"\\.(jpeg|jpg|png|gif|webp)$\">\nAllow from all\n</Files>";
            file_put_contents($htaccessPath, $htaccessContent);
        }
    }

    /**
     * Met à jour le profil d'un utilisateur
     */
    public function updateProfile($user_id, array $data): array {
        try {
            // Validation des données requises
            if (!$this->validateRequiredFields($data, ['nom', 'prenom', 'email'])) {
                return [
                    "success" => false, 
                    "message" => "Tous les champs obligatoires doivent être remplis"
                ];
            }

            // Vérification de l'unicité de l'email
            if ($this->emailExists($data['email'], $user_id)) {
                return [
                    "success" => false, 
                    "message" => "Cet email est déjà utilisé par un autre utilisateur"
                ];
            }

            // Récupérer l'utilisateur existant
            $utilisateur = $this->getUserById($user_id);
            if (!$utilisateur) {
                return [
                    "success" => false, 
                    "message" => "Utilisateur non trouvé"
                ];
            }

            // Mettre à jour les propriétés de l'utilisateur
            $this->updateUserProperties($utilisateur, $data);

            // Sauvegarder les modifications
            if ($this->saveUser($utilisateur)) {
                return [
                    "success" => true, 
                    "message" => "Profil mis à jour avec succès"
                ];
            }

            return [
                "success" => false, 
                "message" => "Erreur lors de la mise à jour"
            ];

        } catch (Exception $e) {
            error_log("Erreur updateProfile: " . $e->getMessage());
            return [
                "success" => false, 
                "message" => "Une erreur est survenue lors de la mise à jour du profil"
            ];
        }
    }

    /**
     * Met à jour la photo de profil
     */
  public function updateProfilePhoto($user_id, array $photo_file): array {
    try {
        // --- 1. Vérification du fichier uploadé ---
        $validation = $this->validateUploadedFile($photo_file);
        if (!$validation['success']) {
            return $validation;
        }

        // --- 2. Récupérer l'utilisateur ---
        $utilisateur = $this->getUserById($user_id);
        if (!$utilisateur) {
            return [
                "success" => false,
                "message" => "Utilisateur non trouvé"
            ];
        }

        // --- 3. Supprimer l'ancienne photo si existante ---
        $this->deleteOldProfilePhoto($utilisateur);

        // --- 4. Générer un nom unique pour la photo ---
        $new_filename = $this->generateProfileFilename($user_id, $photo_file['name']);
        $file_path = $this->uploadDir . $new_filename;

        // --- 5. Déplacer le fichier uploadé ---
        if (!move_uploaded_file($photo_file['tmp_name'], $file_path)) {
            return [
                "success" => false,
                "message" => "Erreur lors de l'enregistrement du fichier"
            ];
        }

        // --- 6. Mettre à jour l'objet utilisateur ---
        $utilisateur->setPhotoProfil($new_filename);

        // --- 7. Sauvegarde avec reconnexion si nécessaire ---
        try {
            if (!$this->saveUser($utilisateur)) {
                throw new Exception("Impossible de sauvegarder l'utilisateur dans la base.");
            }
        } catch (PDOException $e) {
            // Si MySQL a fermé la connexion, on tente de reconnecter
            if (strpos($e->getMessage(), 'MySQL server has gone away') !== false) {
                error_log("Reconnect MySQL après déconnexion: " . $e->getMessage());
                $this->pdo = config::getConnexion(); // Nouvelle connexion
                if (!$this->saveUser($utilisateur)) {
                    // Supprimer la photo uploadée si échec
                    $this->deletePhotoFile($new_filename);
                    return [
                        "success" => false,
                        "message" => "Erreur lors de la mise à jour après reconnexion MySQL"
                    ];
                }
            } else {
                // Autre erreur PDO
                $this->deletePhotoFile($new_filename);
                return [
                    "success" => false,
                    "message" => "Erreur MySQL: " . $e->getMessage()
                ];
            }
        }

        return [
            "success" => true,
            "message" => "Photo de profil mise à jour avec succès",
            "filename" => $new_filename
        ];

    } catch (Exception $e) {
        error_log("Erreur updateProfilePhoto: " . $e->getMessage());
        return [
            "success" => false,
            "message" => "Une erreur est survenue lors de la mise à jour de la photo"
        ];
    }
}


    /**
     * Supprime la photo de profil
     */
    public function deleteProfilePhoto($user_id): array {
        try {
            $utilisateur = $this->getUserById($user_id);
            if (!$utilisateur) {
                return [
                    "success" => false, 
                    "message" => "Utilisateur non trouvé"
                ];
            }

            // Supprimer le fichier physique
            $this->deleteOldProfilePhoto($utilisateur);

            // Mettre à jour l'utilisateur
            $utilisateur->setPhotoProfil(null);
            
            if ($this->saveUser($utilisateur)) {
                return [
                    "success" => true, 
                    "message" => "Photo de profil supprimée avec succès"
                ];
            }

            return [
                "success" => false, 
                "message" => "Erreur lors de la suppression de la photo"
            ];

        } catch (Exception $e) {
            error_log("Erreur deleteProfilePhoto: " . $e->getMessage());
            return [
                "success" => false, 
                "message" => "Une erreur est survenue lors de la suppression de la photo"
            ];
        }
    }

    /**
     * Récupère un utilisateur par son ID
     */
    private function getUserById($user_id): ?Utilisateur {
        try {
            $query = "SELECT * FROM utilisateur WHERE id_utilisateur = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $row ? $this->createUserFromRow($row) : null;
            
        } catch (Exception $e) {
            error_log("Erreur getUserById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crée un objet Utilisateur à partir d'une ligne de base de données
     */
    private function createUserFromRow(array $row): Utilisateur {
        $utilisateur = new Utilisateur(
            $row['nom'],
            $row['prenom'],
            $row['email'],
            '', // Mot de passe vide - nous ne le manipulons pas directement
            $row['dateNaissance'] ?? '',
            $row['adresse'] ?? '',
            $row['role'] ?? 'utilisateur',
            $row['statut'] ?? 'actif'
        );
        
        $utilisateur->setId($row['id_utilisateur'])
                   ->setDateInscription($row['date_inscription'])
                   ->setPhotoProfil($row['photo_profil'] ?? null);
        
        return $utilisateur;
    }

    /**
     * Sauvegarde un utilisateur dans la base de données
     */
    private function saveUser(Utilisateur $utilisateur): bool {
        try {
            $query = "UPDATE utilisateur SET 
                     nom = ?, prenom = ?, email = ?, dateNaissance = ?, 
                     adresse = ?, role = ?, statut = ?, photo_profil = ?
                     WHERE id_utilisateur = ?";
            
            $stmt = $this->pdo->prepare($query);
            
            return $stmt->execute([
                $utilisateur->getNom(),
                $utilisateur->getPrenom(),
                $utilisateur->getEmail(),
                $utilisateur->getDateNaissance(),
                $utilisateur->getAdresse(),
                $utilisateur->getRole(),
                $utilisateur->getStatut(),
                $utilisateur->getPhotoProfil(),
                $utilisateur->getId()
            ]);
            
        } catch (Exception $e) {
            error_log("Erreur saveUser: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un email existe déjà
     */
    private function emailExists($email, $exclude_user_id = null): bool {
        try {
            if ($exclude_user_id) {
                $query = "SELECT COUNT(*) FROM utilisateur 
                         WHERE email = ? AND id_utilisateur != ?";
                $stmt = $this->pdo->prepare($query);
                $stmt->execute([$email, $exclude_user_id]);
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

    /**
     * Valide les champs requis
     */
    private function validateRequiredFields(array $data, array $required_fields): bool {
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Met à jour les propriétés d'un utilisateur
     */
    private function updateUserProperties(Utilisateur $utilisateur, array $data): void {
        $utilisateur->setNom($data['nom'])
                   ->setPrenom($data['prenom'])
                   ->setEmail($data['email'])
                   ->setDateNaissance($data['dateNaissance'] ?? null)
                   ->setAdresse($data['adresse'] ?? null);
    }

    /**
     * Valide un fichier uploadé
     */
    private function validateUploadedFile(array $file): array {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_file_size = 2 * 1024 * 1024; // 2MB

        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                "success" => false,
                "message" => $this->getUploadErrorMessage($file['error'])
            ];
        }

        // Vérifier la taille
        if ($file['size'] > $max_file_size) {
            return [
                "success" => false,
                "message" => "Le fichier est trop volumineux (max 2MB)"
            ];
        }

        // Vérifier le type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($file_type, $allowed_types)) {
            return [
                "success" => false,
                "message" => "Format de fichier non supporté"
            ];
        }

        // Vérifier que c'est une image valide
        if (!getimagesize($file['tmp_name'])) {
            return [
                "success" => false,
                "message" => "Le fichier n'est pas une image valide"
            ];
        }

        return ["success" => true];
    }

    /**
     * Génère un nom de fichier unique pour la photo de profil
     */
    private function generateProfileFilename($user_id, $original_filename): string {
        $extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;
        
        return sprintf(
            'profile_%d_%s_%s.%s',
            $user_id,
            time(),
            bin2hex(random_bytes(8)),
            $extension
        );
    }

    /**
     * Déplace un fichier uploadé
     */
    private function moveUploadedFile($tmp_path, $destination): bool {
        return move_uploaded_file($tmp_path, $destination);
    }

    /**
     * Supprime l'ancienne photo de profil
     */
    private function deleteOldProfilePhoto(Utilisateur $utilisateur): void {
        $old_photo = $utilisateur->getPhotoProfil();
        if ($old_photo) {
            $this->deletePhotoFile($old_photo);
        }
    }

    /**
     * Supprime un fichier photo
     */
    private function deletePhotoFile($filename): bool {
        $file_path = $this->uploadDir . $filename;
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        return false;
    }

    /**
     * Retourne un message d'erreur pour les codes d'erreur d'upload
     */
    private function getUploadErrorMessage($error_code): string {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Le fichier est trop volumineux',
            UPLOAD_ERR_FORM_SIZE => 'Le fichier est trop volumineux',
            UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement uploadé',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été uploadé',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
            UPLOAD_ERR_CANT_WRITE => 'Erreur d\'écriture du fichier',
            UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté l\'upload'
        ];
        
        return $errors[$error_code] ?? 'Erreur inconnue lors de l\'upload';
    }

    /**
     * Obtient l'URL de la photo de profil
     */
    public function getProfilePhotoUrl($photo_filename): ?string {
        if (empty($photo_filename)) {
            return null;
        }

        $file_path = $this->uploadDir . $photo_filename;
        
        if (!file_exists($file_path)) {
            return null;
        }

        return $this->webUploadPath . $photo_filename . '?t=' . filemtime($file_path);
    }

    /**
     * Obtient le chemin absolu de la photo
     */
    public function getProfilePhotoPath($photo_filename): ?string {
        if (empty($photo_filename)) {
            return null;
        }

        $file_path = $this->uploadDir . $photo_filename;
        return file_exists($file_path) ? $file_path : null;
    }

    /**
     * Vérifie si une photo de profil existe
     */
    public function profilePhotoExists($photo_filename) {
        if (empty($photo_filename)) {
            return false;
        }

        $file_path = $this->uploadDir . $photo_filename;
        return file_exists($file_path) && is_file($file_path);
    }
    
    /**
     * Méthode de débogage pour vérifier la configuration
     */
    public function debugConfiguration() {
        return [
            'uploadDir' => $this->uploadDir,
            'webUploadPath' => $this->webUploadPath,
            'uploadDirExists' => is_dir($this->uploadDir),
            'uploadDirWritable' => is_writable($this->uploadDir),
            'serverDocumentRoot' => $_SERVER['DOCUMENT_ROOT'] ?? 'Non défini'
        ];
    }
}
?>