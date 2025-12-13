<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

class MailerService
{
    private $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);

        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'medsense.service@gmail.com'; // SMTP username
        $this->mail->Password = 'bsom jjfs jvcj egat';    // SMTP password
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;

        // Default sender
        $this->mail->setFrom('medsense.service@gmail.com', 'Medsense');
        $this->mail->CharSet = 'UTF-8';
    }

    public function sendConfirmationEmail($toEmail, $userName, $doctorName, $date, $time, $healthTip = null)
    {
        try {
            $this->mail->addAddress($toEmail, $userName);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Confirmation de votre rendez-vous - Medsense';

            $tipHtml = '';
            if ($healthTip) {
                $tipHtml = "
                <div style='background-color: #f0fdf4; border-left: 4px solid #16a34a; padding: 15px; margin-top: 20px; border-radius: 4px;'>
                    <h3 style='color: #166534; margin: 0 0 10px 0; font-size: 16px;'>💡 Le Conseil Santé de l'IA Medsense</h3>
                    <p style='color: #15803d; margin: 0; font-style: italic;'>\"$healthTip\"</p>
                </div>";
            }

            $this->mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto;'>
                    <h1 style='color: #2563eb;'>Rendez-vous Confirmé</h1>
                    <p>Bonjour <strong>$userName</strong>,</p>
                    <p>Votre rendez-vous avec le <strong>Dr. $doctorName</strong> a été confirmé avec succès.</p>
                    
                    <div style='background-color: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                        <p style='margin: 5px 0;'><strong>Date :</strong> $date</p>
                        <p style='margin: 5px 0;'><strong>Heure :</strong> $time</p>
                    </div>

                    $tipHtml

                    <p style='margin-top: 30px;'>Merci de votre confiance.</p>
                    <p style='color: #6b7280; font-size: 0.9em;'>L'équipe Medsense</p>
                </div>
            ";

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }

    public function sendCancellationEmail($toEmail, $userName, $doctorName, $date, $time)
    {
        try {
            $this->mail->addAddress($toEmail, $userName);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Annulation de votre rendez-vous - Medsense';
            $this->mail->Body = "
                <h1>Rendez-vous Annulé</h1>
                <p>Bonjour $userName,</p>
                <p>Votre rendez-vous avec le <strong>Dr. $doctorName</strong> prévu le $date à $time a été annulé avec succès.</p>
                <p>Vous pouvez prendre un nouveau rendez-vous à tout moment sur notre site.</p>
                <p>L'équipe Medsense</p>
            ";

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }

    public function sendReviewResponseEmail($toEmail, $userName, $doctorName, $aiResponse)
    {
        try {
            $this->mail->addAddress($toEmail, $userName);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Réponse du Dr. ' . $doctorName . ' à votre avis - Medsense';

            $this->mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto;'>
                    <div style='text-align: center; margin-bottom: 20px;'>
                        <h2 style='color: #2563eb;'>Nouveau message de votre médecin</h2>
                    </div>
                    
                    <p>Bonjour <strong>$userName</strong>,</p>
                    <p>Le <strong>Dr. $doctorName</strong> a répondu à l'avis que vous avez laissé suite à votre consultation.</p>
                    
                    <div style='background-color: #f8fafc; border-left: 4px solid #3b82f6; padding: 20px; border-radius: 4px; margin: 20px 0;'>
                        <p style='margin: 0; font-style: italic; color: #475569;'>\"$aiResponse\"</p>
                    </div>

                    <p>Nous vous remercions de votre confiance et espérons vous revoir bientôt.</p>
                    <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 30px 0;'>
                    <p style='color: #94a3b8; font-size: 0.85em; text-align: center;'>Ceci est un message automatique généré suite à votre avis.</p>
                </div>
            ";

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}
