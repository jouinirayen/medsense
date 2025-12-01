<?php
require_once __DIR__ . '/../config/config.php';
// MailerService moved to frontend usage


class ReservationController
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = (new config())->getConnexion();
    }

    public function getBookedSlots($doctorId)
    {
        try {
            // Fetch appointments where status is 'pris' (taken)
            $stmt = $this->pdo->prepare("SELECT heureRdv FROM rendezvous WHERE idMedecin = ? AND statut = 'pris'");
            $stmt->execute([$doctorId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            // In a real app, log this error
            return [];
        }
    }

    public function bookSlot(Reservation $reservation)
    {
        try {
            $doctorId = $reservation->getIdMedecin();
            $time = $reservation->getHeureRdv();
            $nom = $reservation->getPatientNom();
            $prenom = $reservation->getPatientPrenom();
            $patientId = $reservation->getIdPatient();

            // Check if slot is already taken
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM rendezvous WHERE idMedecin = ? AND heureRdv = ? AND statut = 'pris'");
            $stmt->execute([$doctorId, $time]);
            if ($stmt->fetchColumn() > 0) {
                return false; // Slot already taken
            }

            // Insert new appointment
            $stmt = $this->pdo->prepare("INSERT INTO rendezvous (idMedecin, heureRdv, patientNom, patientPrenom, statut, idPatient) VALUES (?, ?, ?, ?, 'pris', ?)");
            $result = $stmt->execute([$doctorId, $time, $nom, $prenom, $patientId]);

            if ($result && $patientId) {
                // Return details needed for email
                $stmt = $this->pdo->prepare("SELECT email FROM utilisateur WHERE id_utilisateur = ?");
                $stmt->execute([$patientId]);
                $patientEmail = $stmt->fetchColumn();

                $stmt = $this->pdo->prepare("SELECT nom, prenom FROM utilisateur WHERE id_utilisateur = ?");
                $stmt->execute([$doctorId]);
                $doctor = $stmt->fetch();

                if ($patientEmail && $doctor) {
                    return [
                        'success' => true,
                        'patientEmail' => $patientEmail,
                        'doctorNom' => $doctor['nom'],
                        'doctorPrenom' => $doctor['prenom']
                    ];
                }
            }

            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAppointmentsByPatient($patientId)
    {
        try {
            $sql = "SELECT r.*, u.nom as medecinNom, u.prenom as medecinPrenom, s.name as serviceNom 
                    FROM rendezvous r 
                    LEFT JOIN utilisateur u ON r.idMedecin = u.id_utilisateur 
                    LEFT JOIN services s ON u.idService = s.id 
                    WHERE r.idPatient = ? 
                    ORDER BY r.heureRdv DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$patientId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAppointmentsByDoctor($doctorId)
    {
        try {
            $sql = "SELECT r.*, u.nom as patientNom, u.prenom as patientPrenom, u.email as patientEmail
                    FROM rendezvous r 
                    LEFT JOIN utilisateur u ON r.idPatient = u.id_utilisateur 
                    WHERE r.idMedecin = ? 
                    ORDER BY r.heureRdv ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$doctorId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function cancelAppointment($appointmentId)
    {
        try {
            // Fetch appointment details before deleting for email notification
            $stmt = $this->pdo->prepare("SELECT r.*, u.email as patientEmail, u.nom as patientNom, u.prenom as patientPrenom, 
                                                d.nom as medecinNom, d.prenom as medecinPrenom 
                                         FROM rendezvous r
                                         LEFT JOIN utilisateur u ON r.idPatient = u.id_utilisateur
                                         LEFT JOIN utilisateur d ON r.idMedecin = d.id_utilisateur
                                         WHERE r.idRDV = ?");
            $stmt->execute([$appointmentId]);
            $appt = $stmt->fetch();

            // Delete the appointment
            $stmt = $this->pdo->prepare("DELETE FROM rendezvous WHERE idRDV = ?");
            $success = $stmt->execute([$appointmentId]);

            if ($success && $appt) {
                return $appt; // Return details so frontend can send email
            }

            return $success;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAppointmentById($appointmentId)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT r.*, u.nom as medecinNom, u.prenom as medecinPrenom, u.id_utilisateur as idMedecin, 
                                                u.heure1_debut, u.heure2_debut, u.heure3_debut, u.heure4_debut
                                         FROM rendezvous r 
                                         LEFT JOIN utilisateur u ON r.idMedecin = u.id_utilisateur 
                                         WHERE r.idRDV = ?");
            $stmt->execute([$appointmentId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    public function updateAppointment($appointmentId, Reservation $reservation)
    {
        try {
            $newTime = $reservation->getHeureRdv();

            // Get appointment details to check doctor
            $appt = $this->getAppointmentById($appointmentId);
            if (!$appt)
                return false;

            $doctorId = $appt['idMedecin'];

            // Check if new slot is already taken (excluding the current appointment if it was the same time, but here we assume newTime is different)
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM rendezvous WHERE idMedecin = ? AND heureRdv = ? AND statut = 'pris' AND idRDV != ?");
            $stmt->execute([$doctorId, $newTime, $appointmentId]);
            if ($stmt->fetchColumn() > 0) {
                return false; // Slot already taken
            }

            // Update appointment
            $stmt = $this->pdo->prepare("UPDATE rendezvous SET heureRdv = ? WHERE idRDV = ?");
            return $stmt->execute([$newTime, $appointmentId]);
        } catch (PDOException $e) {
            return false;
        }
    }

}
