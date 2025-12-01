<?php
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../models/UtilisateurRepository.php';

class ProfileController {
    private $utilisateurRepository;
    private $uploadDir;
    private $webUploadPath;

    public function __construct() {
        $this->utilisateurRepository = new UtilisateurRepository(Config::getConnexion());
        
        // CHEMINS CORRIGÉS - utilisez UNE de ces options :
        
        // OPTION 1: Chemin vers uploads à la racine (recommandé)
        $this->uploadDir = __DIR__ . '/../../uploads/profils/';
        $this->webUploadPath = '/uploads/profils/';
        
        // OPTION 2: Ou si vous voulez garder dans views/frontoffice/auth
        // $this->uploadDir = __DIR__ . '/../views/frontoffice/auth/uploads/profils/';
        // $this->webUploadPath = '/projet/views/frontoffice/auth/uploads/profils/';
        
        $this->ensureUploadDirExists();
    }

    private function ensureUploadDirExists() {
        // Vérifier d'abord si le dossier existe déjà
        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                error_log("Erreur: Impossible de créer le dossier " . $this->uploadDir);
                throw new Exception("Impossible de créer le dossier de stockage des photos");
            }
            error_log("Dossier créé: " . $this->uploadDir);
        } else {
            // Le dossier existe déjà, c'est normal
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
     * Met à jour le profil utilisateur
     */
    public function updateProfile($user_id, $data) {
        try {
            // Validation des champs obligatoires
            if (empty($data['nom']) || empty($data['prenom']) || empty($data['email'])) {
                return ["success" => false, "message" => "Tous les champs obligatoires doivent être remplis"];
            }

            // Vérifier si l'email est déjà utilisé par un autre utilisateur
            if ($this->utilisateurRepository->emailExists($data['email'], $user_id)) {
                return ["success" => false, "message" => "Cet email est déjà utilisé par un autre utilisateur"];
            }

            // Récupérer l'utilisateur
            $utilisateur = $this->utilisateurRepository->find($user_id);
            if (!$utilisateur) {
                return ["success" => false, "message" => "Utilisateur non trouvé"];
            }

            // Mettre à jour les champs
            $utilisateur->setNom($data['nom'])
                       ->setPrenom($data['prenom'])
                       ->setEmail($data['email'])
                       ->setDateNaissance($data['dateNaissance'] ?: null)
                       ->setAdresse($data['adresse'] ?: null);

            // Sauvegarder
            if ($this->utilisateurRepository->save($utilisateur)) {
                return ["success" => true, "message" => "Profil mis à jour avec succès"];
            } else {
                return ["success" => false, "message" => "Erreur lors de la mise à jour"];
            }

        } catch (Exception $e) {
            error_log("Erreur updateProfile: " . $e->getMessage());
            return ["success" => false, "message" => "Une erreur est survenue lors de la mise à jour du profil"];
        }
    }

    /**
     * Met à jour la photo de profil
     */
    public function updateProfilePhoto($user_id, $photo_file) {
        try {
            if ($photo_file['error'] !== UPLOAD_ERR_OK) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'Le fichier est trop volumineux',
                    UPLOAD_ERR_FORM_SIZE => 'Le fichier est trop volumineux',
                    UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement uploadé',
                    UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été uploadé',
                    UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
                    UPLOAD_ERR_CANT_WRITE => 'Erreur d\'écriture du fichier',
                    UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté l\'upload'
                ];
                $message = $errorMessages[$photo_file['error']] ?? 'Erreur inconnue lors de l\'upload';
                return ["success" => false, "message" => $message];
            }

            // Vérifications de sécurité
            if (!$this->validateImageFile($photo_file)) {
                return ["success" => false, "message" => "Fichier image invalide. Formats acceptés: JPEG, PNG, GIF, WebP (max 2MB)"];
            }

            // Générer un nom de fichier unique et sécurisé
            $file_extension = strtolower(pathinfo($photo_file['name'], PATHINFO_EXTENSION));
            $safe_extension = $file_extension === 'jpeg' ? 'jpg' : $file_extension;
            $new_filename = 'profile_' . $user_id . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $safe_extension;
            $file_path = $this->uploadDir . $new_filename;

            // Déplacer le fichier uploadé
            if (!move_uploaded_file($photo_file['tmp_name'], $file_path)) {
                error_log("Erreur move_uploaded_file vers: " . $file_path);
                return ["success" => false, "message" => "Erreur lors de l'enregistrement du fichier"];
            }

            // Vérifier que le fichier a bien été créé avant de changer les permissions
            if (file_exists($file_path)) {
                // chmod optionnel sur Windows, mais utile sur Linux
                if (!chmod($file_path, 0644)) {
                    error_log("Warning: Impossible de changer les permissions de " . $file_path);
                }
            } else {
                error_log("Erreur: Fichier non créé après move_uploaded_file: " . $file_path);
                return ["success" => false, "message" => "Erreur lors de l'enregistrement du fichier"];
            }

            // Récupérer l'utilisateur
            $utilisateur = $this->utilisateurRepository->find($user_id);
            if (!$utilisateur) {
                // Supprimer le fichier uploadé si l'utilisateur n'existe pas
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                return ["success" => false, "message" => "Utilisateur non trouvé"];
            }

            // Supprimer l'ancienne photo si elle existe
            $old_photo = $utilisateur->getPhotoProfil();
            if ($old_photo) {
                $this->deletePhotoFile($old_photo);
            }

            // Mettre à jour la photo de profil
            $utilisateur->setPhotoProfil($new_filename);
            if ($this->utilisateurRepository->save($utilisateur)) {
                return [
                    "success" => true, 
                    "message" => "Photo de profil mise à jour avec succès", 
                    "filename" => $new_filename
                ];
            } else {
                // Supprimer le fichier si la sauvegarde en base échoue
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                return ["success" => false, "message" => "Erreur lors de la mise à jour de la photo"];
            }

        } catch (Exception $e) {
            error_log("Erreur updateProfilePhoto: " . $e->getMessage());
            return ["success" => false, "message" => "Une erreur est survenue lors de la mise à jour de la photo"];
        }
    }

    /**
     * Supprime la photo de profil
     */
    public function deleteProfilePhoto($user_id) {
        try {
            $utilisateur = $this->utilisateurRepository->find($user_id);
            if (!$utilisateur) {
                return ["success" => false, "message" => "Utilisateur non trouvé"];
            }

            $old_photo = $utilisateur->getPhotoProfil();
            if ($old_photo) {
                $this->deletePhotoFile($old_photo);
            }

            $utilisateur->setPhotoProfil(null);
            if ($this->utilisateurRepository->save($utilisateur)) {
                return ["success" => true, "message" => "Photo de profil supprimée avec succès"];
            } else {
                return ["success" => false, "message" => "Erreur lors de la suppression de la photo"];
            }

        } catch (Exception $e) {
            error_log("Erreur deleteProfilePhoto: " . $e->getMessage());
            return ["success" => false, "message" => "Une erreur est survenue lors de la suppression de la photo"];
        }
    }

    /**
     * Valide un fichier image uploadé
     */
    private function validateImageFile($file) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_file_size = 2 * 1024 * 1024; // 2MB

        // Vérifier la taille
        if ($file['size'] > $max_file_size) {
            error_log("Fichier trop volumineux: " . $file['size'] . " bytes");
            return false;
        }

        // Vérifier le type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($file_type, $allowed_types)) {
            error_log("Type MIME non autorisé: " . $file_type);
            return false;
        }

        // Vérifier l'extension
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_extensions)) {
            error_log("Extension non autorisée: " . $file_extension);
            return false;
        }

        // Vérifier que c'est bien une image
        $image_info = getimagesize($file['tmp_name']);
        if (!$image_info) {
            error_log("Fichier n'est pas une image valide");
            return false;
        }

        return true;
    }

    /**
     * Supprime un fichier photo physiquement
     */
    private function deletePhotoFile($filename) {
        $file_path = $this->uploadDir . $filename;
        if (file_exists($file_path) && is_file($file_path)) {
            if (!unlink($file_path)) {
                error_log("Erreur lors de la suppression de " . $file_path);
                return false;
            }
            return true;
        } else {
            error_log("Fichier à supprimer non trouvé: " . $file_path);
            return false;
        }
    }

    /**
     * Obtient l'URL complète de la photo de profil pour l'affichage
     */
    public function getProfilePhotoUrl($photo_filename) {
        if (empty($photo_filename)) {
            return null;
        }

        $file_path = $this->uploadDir . $photo_filename;
        
        // Vérifier si le fichier existe physiquement
        if (!file_exists($file_path) || !is_file($file_path)) {
            error_log("Photo de profil non trouvée: " . $file_path);
            return null;
        }

        // Vérifier les permissions de lecture
        if (!is_readable($file_path)) {
            error_log("Photo de profil non lisible: " . $file_path);
            return null;
        }

        // Retourner le chemin web avec un timestamp pour éviter le cache
        $timestamp = filemtime($file_path);
        
        // Construction de l'URL selon l'option choisie
        if ($this->webUploadPath[0] === '/') {
            // URL absolue
            $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") 
                        . "://" . $_SERVER['HTTP_HOST'];
            return $base_url . $this->webUploadPath . $photo_filename . '?t=' . $timestamp;
        } else {
            // URL relative
            return $this->webUploadPath . $photo_filename . '?t=' . $timestamp;
        }
    }

    /**
     * Obtient le chemin absolu du fichier photo
     */
    public function getProfilePhotoPath($photo_filename) {
        if (empty($photo_filename)) {
            return null;
        }

        $file_path = $this->uploadDir . $photo_filename;
        
        if (file_exists($file_path) && is_file($file_path)) {
            return $file_path;
        }
        
        return null;
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