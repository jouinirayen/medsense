<?php
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../config/config.php';

class ProfileController
{
    private $pdo;
    private $uploadDir;
    private $webUploadPath;

    public function __construct()
    {
        $this->pdo = (new config())->getConnexion();

        $this->uploadDir = __DIR__ . '/../../uploads/profiles/';
        $this->webUploadPath = '/uploads/profiles/';
        $this->ensureUploadDirExists();
    }

    private function ensureUploadDirExists()
    {
        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                error_log("Erreur: Impossible de créer le dossier " . $this->uploadDir);
                throw new Exception("Impossible de créer le dossier de stockage des photos");
            }
            error_log("Dossier créé: " . $this->uploadDir);
        } else {
            error_log("Dossier existe déjà: " . $this->uploadDir);
        }

        $indexFile = $this->uploadDir . 'index.html';
        if (!file_exists($indexFile)) {
            file_put_contents($indexFile, '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><h1>Forbidden</h1><p>You don\'t have permission to access this resource.</p></body></html>');
        }

        $htaccessPath = $this->uploadDir . '.htaccess';
        if (!file_exists($htaccessPath)) {
            $htaccessContent = "Order deny,allow\nDeny from all\n<Files ~ \"\\.(jpeg|jpg|png|gif|webp)$\">\nAllow from all\n</Files>";
            file_put_contents($htaccessPath, $htaccessContent);
        }
    }

    public function updateProfile($user_id, array $data): array
    {
        try {
            if (!$this->validateRequiredFields($data, ['nom', 'prenom', 'email'])) {
                return [
                    "success" => false,
                    "message" => "Tous les champs obligatoires doivent être remplis"
                ];
            }

            if ($this->emailExists($data['email'], $user_id)) {
                return [
                    "success" => false,
                    "message" => "Cet email est déjà utilisé par un autre utilisateur"
                ];
            }

            $utilisateur = $this->getUserById($user_id);
            if (!$utilisateur) {
                return [
                    "success" => false,
                    "message" => "Utilisateur non trouvé"
                ];
            }

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

    public function updateProfilePhoto($user_id, array $photo_file): array
    {
        try {
            $validation = $this->validateUploadedFile($photo_file);
            if (!$validation['success']) {
                return $validation;
            }

            $utilisateur = $this->getUserById($user_id);
            if (!$utilisateur) {
                return [
                    "success" => false,
                    "message" => "Utilisateur non trouvé"
                ];
            }

            $this->deleteOldProfilePhoto($utilisateur);

            $new_filename = $this->generateProfileFilename($user_id, $photo_file['name']);
            $file_path = $this->uploadDir . $new_filename;

            if (!move_uploaded_file($photo_file['tmp_name'], $file_path)) {
                return [
                    "success" => false,
                    "message" => "Erreur lors de l'enregistrement du fichier"
                ];
            }

            $utilisateur->setImage($new_filename);

            try {
                if (!$this->saveUserPhoto($utilisateur)) {
                    // Supprimer la photo uploadée si échec
                    $this->deletePhotoFile($new_filename);
                    return [
                        "success" => false,
                        "message" => "Erreur lors de la mise à jour de la photo dans la base de données"
                    ];
                }
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'MySQL server has gone away') !== false) {
                    error_log("Reconnect MySQL après déconnexion: " . $e->getMessage());
                    $this->pdo = config::getConnexion(); // Nouvelle connexion
                    if (!$this->saveUserPhoto($utilisateur)) {
                        $this->deletePhotoFile($new_filename);
                        return [
                            "success" => false,
                            "message" => "Erreur lors de la mise à jour après reconnexion MySQL"
                        ];
                    }
                } else {
                    $this->deletePhotoFile($new_filename);
                    return [
                        "success" => false,
                        "message" => "Erreur MySQL: " . $e->getMessage()
                    ];
                }
            }

            $photo_url = $this->getProfilePhotoUrl($new_filename);
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['photo_url'] = $photo_url;

            return [
                "success" => true,
                "message" => "Photo de profil mise à jour avec succès",
                "filename" => $new_filename,
                "photo_url" => $photo_url
            ];

        } catch (Exception $e) {
            error_log("Erreur updateProfilePhoto: " . $e->getMessage());
            return [
                "success" => false,
                "message" => "Une erreur est survenue lors de la mise à jour de la photo"
            ];
        }
    }

    public function deleteProfilePhoto($user_id): array
    {
        try {
            $utilisateur = $this->getUserById($user_id);
            if (!$utilisateur) {
                return [
                    "success" => false,
                    "message" => "Utilisateur non trouvé"
                ];
            }

            $this->deleteOldProfilePhoto($utilisateur);

            $utilisateur->setImage(null);

            if ($this->saveUserPhoto($utilisateur)) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['photo_url'] = null;

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

    private function getUserById($user_id): ?Utilisateur
    {
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

    private function createUserFromRow(array $row): Utilisateur
    {
        $utilisateur = new Utilisateur(
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

        $utilisateur->setId($row['id_utilisateur'])
            ->setDateInscription($row['date_inscription'] ?? date('Y-m-d H:i:s'))
            ->setMotDePasse($row['mot_de_passe'], true)
            ->setResetToken($row['reset_token'] ?? null)
            ->setResetTokenExpires($row['reset_token_expires'] ?? null)
            ->setHistoriqueConnexions($row['historique_connexions'] ?? null)
            ->setDiplomeStatut($row['diplome_statut'] ?? 'en attente')
            ->setDiplomeCommentaire($row['diplome_commentaire'] ?? null)
            ->setDiplomeDateVerification($row['diplome_date_verification'] ?? null)
            ->setDerniereConnexion($row['derniere_connexion'] ?? null);

        return $utilisateur;
    }

    private function saveUser(Utilisateur $utilisateur): bool
    {
        try {
            $query = "UPDATE utilisateur SET 
                     nom = ?, prenom = ?, email = ?, dateNaissance = ?, 
                     adresse = ?, role = ?, statut = ?, bio = ?, 
                     idService = ?, username = ?, specialite = ?,
                     date_inscription = ?, reset_token = ?, reset_token_expires = ?,
                     diplome_path = ?, historique_connexions = ?, diplome_statut = ?,
                     diplome_commentaire = ?, diplome_date_verification = ?,
                     derniere_connexion = ?, heure1_debut = ?, heure1_fin = ?,
                     heure2_debut = ?, heure2_fin = ?, heure3_debut = ?,
                     heure3_fin = ?, heure4_debut = ?, heure4_fin = ?,
                     note_globale = ?, nb_avis = ?, langues = ?,
                     prix_consultation = ?, experience = ?, image = ?
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
                $utilisateur->getBio(),
                $utilisateur->getIdService(),
                $utilisateur->getUsername(),
                $utilisateur->getSpecialite(),
                $utilisateur->getDateInscription(),
                $utilisateur->getResetToken(),
                $utilisateur->getResetTokenExpires(),
                $utilisateur->getDiplomePath(),
                $utilisateur->getHistoriqueConnexions(),
                $utilisateur->getDiplomeStatut(),
                $utilisateur->getDiplomeCommentaire(),
                $utilisateur->getDiplomeDateVerification(),
                $utilisateur->getDerniereConnexion(),
                $utilisateur->getHeure1Debut(),
                $utilisateur->getHeure1Fin(),
                $utilisateur->getHeure2Debut(),
                $utilisateur->getHeure2Fin(),
                $utilisateur->getHeure3Debut(),
                $utilisateur->getHeure3Fin(),
                $utilisateur->getHeure4Debut(),
                $utilisateur->getHeure4Fin(),
                $utilisateur->getNoteGlobale(),
                $utilisateur->getNbAvis(),
                $utilisateur->getLangues(),
                $utilisateur->getPrixConsultation(),
                $utilisateur->getExperience(),
                $utilisateur->getImage(),
                $utilisateur->getId()
            ]);

        } catch (Exception $e) {
            error_log("Erreur saveUser: " . $e->getMessage());
            return false;
        }
    }

    private function saveUserPhoto(Utilisateur $utilisateur): bool
    {
        try {
            $query = "UPDATE utilisateur SET 
                     image = ?
                     WHERE id_utilisateur = ?";

            $stmt = $this->pdo->prepare($query);

            return $stmt->execute([
                $utilisateur->getImage(),
                $utilisateur->getId()
            ]);

        } catch (Exception $e) {
            error_log("Erreur saveUserPhoto: " . $e->getMessage());
            return false;
        }
    }

    private function emailExists($email, $exclude_user_id = null): bool
    {
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

    private function validateRequiredFields(array $data, array $required_fields): bool
    {
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }

    private function updateUserProperties(Utilisateur $utilisateur, array $data): void
    {
        $utilisateur->setNom($data['nom'])
            ->setPrenom($data['prenom'])
            ->setEmail($data['email'])
            ->setDateNaissance($data['dateNaissance'] ?? null)
            ->setAdresse($data['adresse'] ?? null)
            ->setBio($data['bio'] ?? null);

        if (isset($data['username'])) {
            $utilisateur->setUsername($data['username']);
        }

        if (isset($data['specialite'])) {
            $utilisateur->setSpecialite($data['specialite']);
        }
    }

    private function validateUploadedFile(array $file): array
    {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_file_size = 2 * 1024 * 1024; // 2MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                "success" => false,
                "message" => $this->getUploadErrorMessage($file['error'])
            ];
        }

        if ($file['size'] > $max_file_size) {
            return [
                "success" => false,
                "message" => "Le fichier est trop volumineux (max 2MB)"
            ];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($file_type, $allowed_types)) {
            return [
                "success" => false,
                "message" => "Format de fichier non supporté"
            ];
        }

        if (!getimagesize($file['tmp_name'])) {
            return [
                "success" => false,
                "message" => "Le fichier n'est pas une image valide"
            ];
        }

        return ["success" => true];
    }

    private function generateProfileFilename($user_id, $original_filename): string
    {
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

    private function deleteOldProfilePhoto(Utilisateur $utilisateur): void
    {
        $old_photo = $utilisateur->getImage();
        if ($old_photo) {
            $this->deletePhotoFile($old_photo);
        }
    }

    private function deletePhotoFile($filename): bool
    {
        $file_path = $this->uploadDir . $filename;
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        return false;
    }

    private function getUploadErrorMessage($error_code): string
    {
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

    public function getProfilePhotoUrl($photo_filename): ?string
    {
        if (empty($photo_filename)) {
            return null;
        }

        $file_path = $this->uploadDir . $photo_filename;

        if (!file_exists($file_path)) {
            return null;
        }

        return $this->webUploadPath . $photo_filename . '?t=' . filemtime($file_path);
    }

    public function getProfilePhotoPath($photo_filename): ?string
    {
        if (empty($photo_filename)) {
            return null;
        }

        $file_path = $this->uploadDir . $photo_filename;
        return file_exists($file_path) ? $file_path : null;
    }

    public function profilePhotoExists($photo_filename)
    {
        if (empty($photo_filename)) {
            return false;
        }

        $file_path = $this->uploadDir . $photo_filename;
        return file_exists($file_path) && is_file($file_path);
    }

    public function debugConfiguration()
    {
        return [
            'uploadDir' => $this->uploadDir,
            'webUploadPath' => $this->webUploadPath,
            'uploadDirExists' => is_dir($this->uploadDir),
            'uploadDirWritable' => is_writable($this->uploadDir),
            'serverDocumentRoot' => $_SERVER['DOCUMENT_ROOT'] ?? 'Non défini'
        ];
    }
    // AJOUTE CETTE MÉTHODE APRÈS getProfilePhotoUrl()
public function getProfileImageUrl($photo_filename): ?string
{
    // Cette méthode est un alias pour getProfilePhotoUrl
    // Elle permet de maintenir la compatibilité avec le code existant
    return $this->getProfilePhotoUrl($photo_filename);
}
}
?>