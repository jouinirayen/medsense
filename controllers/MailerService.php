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

    public function sendConfirmationEmail($toEmail, $userName, $doctorName, $date, $time)
    {
        try {
            $this->mail->addAddress($toEmail, $userName);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Confirmation de votre rendez-vous - Medsense';
            $this->mail->Body = "
                <h1>Rendez-vous Confirmé</h1>
                <p>Bonjour $userName,</p>
                <p>Votre rendez-vous avec le <strong>Dr. $doctorName</strong> a été confirmé.</p>
                <p><strong>Date :</strong> $date</p>
                <p><strong>Heure :</strong> $time</p>
                <p>Merci de votre confiance.</p>
                <p>L'équipe Medsense</p>
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
}
