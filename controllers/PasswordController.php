<?php
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../models/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../models/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PasswordController {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }
    public function forgotPassword($email) {
        try {
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Adresse email invalide.'];
            }

            $user = $this->findUserByEmail($email);

            if (!$user) {
                return ['success' => true, 'message' => 'Si votre adresse email existe, vous recevrez un lien de réinitialisation.'];
            }

            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            if (!$this->savePasswordResetToken($user->getId(), $token, $expires)) {
                return ['success' => false, 'message' => 'Erreur lors de la génération du token.'];
            }

            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/projet/views/frontoffice/auth/reset-password.php?token=" . $token;

            $emailSent = $this->sendResetEmail($user->getEmail(), $user->getPrenom() . ' ' . $user->getNom(), $resetLink);

            if ($emailSent) {
                return ['success' => true, 'message' => 'Un email contenant le lien de réinitialisation a été envoyé à votre adresse.'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer.'];
            }

        } catch (Exception $e) {
            error_log("Erreur forgotPassword: " . $e->getMessage());
            return ['success' => false, 'message' => 'Une erreur est survenue.'];
        }
    }

   
   private function sendResetEmail($toEmail, $userName, $resetLink) {
    $mail = new PHPMailer(true);
    try {
        
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'eyamis2005@gmail.com';
        $mail->Password = 'zauv viic oims gqwh';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        
        $mail->setFrom('eyamis2005@gmail.com', 'MedCare Support');
        $mail->addAddress($toEmail, $userName);
        $mail->addReplyTo('support@medsense.com', 'Support');
        
        $mail->isHTML(true);
        $mail->Subject = 'Réinitialisation de votre mot de passe - MedCare';
        $mail->Body = $this->getResetEmailTemplate($userName, $resetLink);
        $mail->AltBody = "Bonjour $userName,\n\nCliquez sur ce lien pour réinitialiser votre mot de passe : $resetLink\n\nCe lien expirera dans 1 heure.";
        
        if (!$mail->send()) {
            error_log("Échec envoi email à $toEmail : " . $mail->ErrorInfo);
            return false;
        }
        
        error_log("Email de réinitialisation envoyé à $toEmail");
        return true;
        
    } catch (Exception $e) {
        error_log("Erreur PHPMailer: " . $e->getMessage());
        error_log("SMTP Error: " . $mail->ErrorInfo);
        return false;
    }
}

private function getResetEmailTemplate($userName, $resetLink) {
    return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #2c3e50;'>Bonjour " . htmlspecialchars($userName) . ",</h2>
            <p>Vous avez demandé la réinitialisation de votre mot de passe pour votre compte MedCare.</p>
            <p>Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='$resetLink' style='background-color: #4CAF50; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                    Réinitialiser mon mot de passe
                </a>
            </div>
            
            <p>Ou copiez-collez ce lien dans votre navigateur :</p>
            <p style='background-color: #f8f9fa; padding: 10px; border-radius: 5px; word-break: break-all;'>
                $resetLink
            </p>
            
            <p><strong>Important :</strong> Ce lien expirera dans 1 heure.</p>
            <p>Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email.</p>
            
            <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
            <p style='color: #777; font-size: 12px;'>
                Cet email a été envoyé automatiquement. Merci de ne pas y répondre.
            </p>
        </div>
    ";
}

 
    public function validateToken($token) {
        $token_hash = hash('sha256', $token);
        $query = $this->pdo->prepare("SELECT id_utilisateur, reset_token_expires FROM utilisateur WHERE reset_token = ?");
        $query->execute([$token_hash]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if (!$user) return ["success" => false, "message" => "Token invalide"];
        if (strtotime($user['reset_token_expires']) < time()) return ["success" => false, "message" => "Le lien a expiré"];

        return ["success" => true, "user_id" => $user['id_utilisateur']];
    }

 
    public function resetPassword($token, $newPassword) {
        try {
            $check = $this->validateToken($token);
            if (!$check['success']) return $check;

            $userId = $check['user_id'];
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $query = $this->pdo->prepare("SELECT email, nom, prenom FROM utilisateur WHERE id_utilisateur = ?");
            $query->execute([$userId]);
            $user = $query->fetch(PDO::FETCH_ASSOC);

            $update = $this->pdo->prepare("
                UPDATE utilisateur SET mot_de_passe = ?, reset_token = NULL, reset_token_expires = NULL WHERE id_utilisateur = ?
            ");
            $update->execute([$hashedPassword, $userId]);

            
            $this->sendConfirmationEmail($user['email'], $user['prenom'] . ' ' . $user['nom']);

            return ["success" => true, "message" => "Mot de passe réinitialisé. Email de confirmation envoyé."];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur : " . $e->getMessage()];
        }
    }

   
    private function sendConfirmationEmail($toEmail, $userName) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'eyamis2005@gmail.com';
            $mail->Password = 'zauv viic oims gqwh';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('eyamis2005@gmail.com', 'stmp');
            $mail->addAddress($toEmail, $userName);

            $mail->isHTML(true);
            $mail->Subject = 'Confirmation de changement de mot de passe - MedCare';
            $mail->Body = "
                <h2>Bonjour " . htmlspecialchars($userName) . ",</h2>
                <p>Votre mot de passe a été changé avec succès.</p>
                <p>Si vous n'avez pas fait cette action, contactez notre support immédiatement.</p>
            ";

            return $mail->send();
        } catch (Exception $e) {
            error_log("Erreur PHPMailer confirmationEmail: " . $mail->ErrorInfo);
            return false;
        }
    }

    
    private function findUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $user = new Utilisateur();
            $user->setId($row['id_utilisateur']);
            $user->setNom($row['nom']);
            $user->setPrenom($row['prenom']);
            $user->setEmail($row['email']);
            return $user;
        }
        return null;
    }

    private function savePasswordResetToken($userId, $token, $expires) {
        $token_hash = hash('sha256', $token);
        $stmt = $this->pdo->prepare("UPDATE utilisateur SET reset_token = ?, reset_token_expires = ? WHERE id_utilisateur = ?");
        return $stmt->execute([$token_hash, $expires, $userId]);
    }
}
