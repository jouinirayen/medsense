<?php
require_once '../../config/config.php';

require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class RendezvousController
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = (new config())->getConnexion();
    }

    private function getMailer(): PHPMailer {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'medsense.service@gmail.com';
            $mail->Password = 'bsom jjfs jvcj egat';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom('medsense.service@gmail.com', 'Medsense');
        } catch (Exception $e) {
            throw new Exception('Impossible de configurer PHPMailer: ' . $e->getMessage());
        }
        return $mail;
    }

    public function envoyerConfirmationEmail($destinataire, $service, $date, $heure) {
        try {
            $mail = $this->getMailer();
            $mail->addAddress($destinataire);
            $mail->Subject = "Confirmation de rendez-vous - $service";
            $mail->isHTML(true);
            $mail->Body = "
                <h2>Confirmation de votre rendez-vous</h2>
                <p><strong>Service :</strong> $service</p>
                <p><strong>Date :</strong> $date</p>
                <p><strong>Heure :</strong> $heure</p>
                <p>Merci d'utiliser notre plateforme.</p>
            ";
            $mail->AltBody = "Service: $service\nDate: $date\nHeure: $heure\nMerci d'utiliser notre plateforme.";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Erreur envoi email: ' . $e->getMessage());
            return false;
        }
    }

    public function obtenirRendezVousDisponibles($serviceId) {
        if (empty($serviceId)) {
            return [];
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT id, service_id, appointment_date, appointment_time
                FROM rendezvous
                WHERE service_id = ? AND is_booked = 0
                ORDER BY appointment_date, appointment_time
            ");
            $stmt->execute([$serviceId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die("Erreur lors de la récupération des rendez-vous: " . $e->getMessage());
        }
    }

    public function reserverCreneau($serviceId, $appointmentDate, $appointmentTime, $email) {
        if (empty($serviceId) || empty($appointmentDate) || empty($appointmentTime) || empty($email)) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare("
                UPDATE rendezvous
                SET is_booked = 1,
                    booked_email = ?
                WHERE service_id = ?
                  AND appointment_date = ?
                  AND appointment_time = ?
                  AND is_booked = 0
                LIMIT 1
            ");
            $stmt->execute([$email, $serviceId, $appointmentDate, $appointmentTime]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            die("Erreur lors de la réservation du créneau: " . $e->getMessage());
        }
    }

    public function obtenirRendezVousParEmail($email) {
        if (empty($email)) {
            return $this->obtenirTousLesRendezVousReserves();
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT r.*, s.name AS service_name, s.description AS service_description
                FROM rendezvous r
                JOIN services s ON s.id = r.service_id
                WHERE r.is_booked = 1 AND r.booked_email = ?
                ORDER BY r.appointment_date, r.appointment_time
            ");
            $stmt->execute([$email]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die("Erreur lors de la récupération des rendez-vous: " . $e->getMessage());
        }
    }

    public function obtenirTousLesRendezVousReserves() {
        try {
            $stmt = $this->pdo->query("
                SELECT r.*, s.name AS service_name, s.description AS service_description
                FROM rendezvous r
                JOIN services s ON s.id = r.service_id
                WHERE r.is_booked = 1
                ORDER BY r.appointment_date, r.appointment_time
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die("Erreur lors de la récupération des rendez-vous: " . $e->getMessage());
        }
    }

    public function annulerRendezVous($slotId, $email = null) {
        if (empty($slotId)) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare("
                UPDATE rendezvous
                SET is_booked = 0, booked_email = NULL
                WHERE id = ?
                " . ($email ? "AND booked_email = ?" : "") . "
                LIMIT 1
            ");
            $params = [$slotId];
            if ($email) {
                $params[] = $email;
            }
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            die("Erreur lors de l'annulation du rendez-vous: " . $e->getMessage());
        }
    }

    public function obtenirTousLesCreneaux()
    {
        try {
            $sql = "
                SELECT r.*, s.name AS service_name
                FROM rendezvous r
                JOIN services s ON s.id = r.service_id
                ORDER BY r.appointment_date, r.appointment_time
            ";
            return $this->pdo->query($sql)->fetchAll();
        } catch (PDOException $e) {
            die("Erreur lors de la récupération des créneaux : " . $e->getMessage());
        }
    }

    public function obtenirCreneauParId($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM rendezvous WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            die("Erreur lors de la récupération du créneau : " . $e->getMessage());
        }
    }

    public function obtenirServices()
    {
        try {
            $stmt = $this->pdo->query("SELECT id, name FROM services ORDER BY name ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die("Erreur lors de la récupération des services : " . $e->getMessage());
        }
    }

    public function ajouterCreneau($serviceId, $date, $time, $isBooked = 0)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO rendezvous (service_id, appointment_date, appointment_time, is_booked)
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([$serviceId, $date, $time, $isBooked]);
        } catch (PDOException $e) {
            die("Erreur lors de l'ajout du créneau : " . $e->getMessage());
        }
    }



    public function modifierCreneau($id, $serviceId, $date, $time, $isBooked = 0)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE rendezvous
                SET service_id = ?, appointment_date = ?, appointment_time = ?, is_booked = ?
                WHERE id = ?
            ");
            return $stmt->execute([$serviceId, $date, $time, $isBooked, $id]);
        } catch (PDOException $e) {
            die("Erreur lors de la modification du créneau : " . $e->getMessage());
        }
    }



    public function supprimerCreneau($id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM rendezvous WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            die("Erreur lors de la suppression du créneau : " . $e->getMessage());
        }
    }
}
?>
