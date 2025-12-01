<?php
// controllers/PasswordController.php
require_once __DIR__ . '/../config.php';

class PasswordController {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function forgotPassword($email) {
        try {
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ["success" => false, "message" => "Adresse email invalide"];
            }

            // Vérifier si l'email existe
            $stmt = $this->pdo->prepare("SELECT id_utilisateur, statut FROM utilisateur WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                return ["success" => true, "message" => "Si l'email existe, un lien a été envoyé"];
            }

            if ($user['statut'] !== 'actif') {
                return ["success" => false, "message" => "Compte non actif"];
            }

            // Générer token
            $reset_token = bin2hex(random_bytes(32));
            $reset_token_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Stocker token
            $stmt = $this->pdo->prepare("UPDATE utilisateur SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
            $success = $stmt->execute([$reset_token, $reset_token_expires, $email]);

            if (!$success) {
                throw new Exception("Erreur base de données");
            }

            // Lien de réinitialisation
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $reset_link = "{$protocol}://{$host}/projet/views/frontoffice/auth/reset-password.php?token={$reset_token}";
            
            return [
                "success" => true, 
                "message" => "Si l'email existe, un lien a été envoyé",
                "reset_link" => $reset_link
            ];

        } catch (Exception $e) {
            error_log("Erreur forgotPassword: " . $e->getMessage());
            return ["success" => false, "message" => "Erreur serveur"];
        }
    }

    public function validateToken($token) {
        try {
            if (empty($token)) return false;

            $token = trim($token);
            
            $stmt = $this->pdo->prepare("
                SELECT id_utilisateur, email 
                FROM utilisateur 
                WHERE reset_token = ? AND reset_token_expires > NOW()
            ");
            $stmt->execute([$token]);
            $user = $stmt->fetch();

            return $user ? $user : false;

        } catch (Exception $e) {
            error_log("Erreur validateToken: " . $e->getMessage());
            return false;
        }
    }

    public function resetPassword($token, $new_password) {
        try {
            // Validation basique
            if (empty($new_password) || strlen($new_password) < 6) {
                return ["success" => false, "message" => "Le mot de passe doit contenir au moins 6 caractères"];
            }

            // Vérifier token
            $user = $this->validateToken($token);
            if (!$user) {
                return ["success" => false, "message" => "Lien invalide ou expiré"];
            }

            // Hasher mot de passe
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Mettre à jour (CORRECTION IMPORTANTE)
            $stmt = $this->pdo->prepare("
                UPDATE utilisateur 
                SET mot_de_passe = ?, reset_token = NULL, reset_token_expires = NULL 
                WHERE id_utilisateur = ?
            ");
            $success = $stmt->execute([$hashed_password, $user['id_utilisateur']]);
            
            $rowCount = $stmt->rowCount();

            if (!$success || $rowCount === 0) {
                throw new Exception("Échec mise à jour mot de passe");
            }

            return ["success" => true, "message" => "Mot de passe réinitialisé avec succès"];

        } catch (Exception $e) {
            error_log("Erreur resetPassword: " . $e->getMessage());
            return ["success" => false, "message" => "Erreur lors de la réinitialisation"];
        }
    }
}
?>v