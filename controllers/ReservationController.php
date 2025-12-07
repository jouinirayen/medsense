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
            // Fetch appointments where status is taken/pending
            $stmt = $this->pdo->prepare("SELECT date, heureRdv FROM rendezvous WHERE idMedecin = ? AND statut IN ('pris', 'confirme', 'en attente', 'termine')");
            $stmt->execute([$doctorId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch as associative array
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
            $date = $reservation->getDate(); // Get date

            // Check if slot is already taken for that date users (if date is used in unique constraint)
            // Note: Ideally unique constraint is (doctor, date, time)
            // Check if slot is already taken for that date users (if date is used in unique constraint)
            // Note: Ideally unique constraint is (doctor, date, time)
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM rendezvous WHERE idMedecin = ? AND heureRdv = ? AND date = ? AND statut IN ('pris', 'confirme', 'en attente', 'termine')");
            $stmt->execute([$doctorId, $time, $date]);
            if ($stmt->fetchColumn() > 0) {
                return false; // Slot already taken
            }

            // Fetch doctor's service ID
            $stmtService = $this->pdo->prepare("SELECT idService FROM utilisateur WHERE id_utilisateur = ?");
            $stmtService->execute([$doctorId]);
            $serviceId = $stmtService->fetchColumn();

            // Insert new appointment with 'en attente' status and idService
            $stmt = $this->pdo->prepare("INSERT INTO rendezvous (idMedecin, heureRdv, patientNom, patientPrenom, statut, idPatient, date, idService) VALUES (?, ?, ?, ?, 'en attente', ?, ?, ?)");
            $result = $stmt->execute([$doctorId, $time, $nom, $prenom, $patientId, $date, $serviceId]);

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
                    WHERE r.idPatient = ? AND r.deleted_by_patient = 0
                    ORDER BY r.heureRdv DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$patientId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAppointmentsByDoctor($doctorId, $date = null)
    {
        try {
            $sql = "SELECT r.*, u.nom as patientNom, u.prenom as patientPrenom, u.email as patientEmail
                    FROM rendezvous r 
                    LEFT JOIN utilisateur u ON r.idPatient = u.id_utilisateur 
                    WHERE r.idMedecin = ?";

            $params = [$doctorId];

            if ($date) {
                $sql .= " AND r.date = ?";
                $params[] = $date;
            }

            $sql .= " ORDER BY r.heureRdv ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
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
                                                p.email as patientEmail,
                                                u.heure1_debut, u.heure2_debut, u.heure3_debut, u.heure4_debut
                                         FROM rendezvous r 
                                         LEFT JOIN utilisateur u ON r.idMedecin = u.id_utilisateur 
                                         LEFT JOIN utilisateur p ON r.idPatient = p.id_utilisateur
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
            $newDate = $reservation->getDate(); // Get date

            // Get appointment details to check doctor
            $appt = $this->getAppointmentById($appointmentId);
            if (!$appt)
                return false;

            $doctorId = $appt['idMedecin'];

            // Check if new slot is already taken (excluding the current appointment)
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM rendezvous WHERE idMedecin = ? AND date = ? AND heureRdv = ? AND statut IN ('pris', 'confirme', 'en attente', 'termine') AND idRDV != ?");
            $stmt->execute([$doctorId, $newDate, $newTime, $appointmentId]);
            if ($stmt->fetchColumn() > 0) {
                return false; // Slot already taken
            }

            // Update appointment
            $stmt = $this->pdo->prepare("UPDATE rendezvous SET date = ?, heureRdv = ? WHERE idRDV = ?");
            return $stmt->execute([$newDate, $newTime, $appointmentId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateStatus($appointmentId, $status)
    {
        try {
            // Validate status
            if (!in_array($status, ['confirme', 'en attente', 'annule', 'termine'])) {
                return false;
            }
            $stmt = $this->pdo->prepare("UPDATE rendezvous SET statut = ? WHERE idRDV = ?");
            $result = $stmt->execute([$status, $appointmentId]);

            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }
    public function rateAppointment($appointmentId, $rating, $patientId)
    {
        try {
            // 1. Get Appointment Details (Verification)
            $stmt = $this->pdo->prepare("SELECT idMedecin, idPatient, statut, note FROM rendezvous WHERE idRDV = ?");
            $stmt->execute([$appointmentId]);
            $appt = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$appt) {
                return ['success' => false, 'error' => 'Rendez-vous introuvable'];
            }

            // Security Checks
            if ($appt['idPatient'] != $patientId) {
                return ['success' => false, 'error' => 'Non autorisé'];
            }
            if ($appt['statut'] !== 'termine') {
                return ['success' => false, 'error' => "Le rendez-vous n'est pas terminé"];
            }
            if ($appt['note'] !== null) {
                return ['success' => false, 'error' => 'Déjà noté'];
            }

            // 2. Update Appointment Rating
            $stmtUpdate = $this->pdo->prepare("UPDATE rendezvous SET note = ? WHERE idRDV = ?");
            $stmtUpdate->execute([$rating, $appointmentId]);

            // 3. Update Doctor's Global Rating
            // Fetch current doctor stats
            $stmtDoc = $this->pdo->prepare("SELECT note_globale, nb_avis FROM utilisateur WHERE id_utilisateur = ?");
            $stmtDoc->execute([$appt['idMedecin']]);
            $doc = $stmtDoc->fetch(PDO::FETCH_ASSOC);

            $currentAvg = floatval($doc['note_globale'] ?? 0);
            $currentCount = intval($doc['nb_avis'] ?? 0);

            // Calculate new average
            $newCount = $currentCount + 1;
            $newAvg = (($currentAvg * $currentCount) + $rating) / $newCount;

            $stmtDocUpdate = $this->pdo->prepare("UPDATE utilisateur SET note_globale = ?, nb_avis = ? WHERE id_utilisateur = ?");
            $stmtDocUpdate->execute([$newAvg, $newCount, $appt['idMedecin']]);

            return ['success' => true];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Erreur base de données'];
        }
    }
    public function clearHistory($patientId)
    {
        try {
            // Only 'delete' (hide) appointments that are cancelled or completed
            $stmt = $this->pdo->prepare("UPDATE rendezvous SET deleted_by_patient = 1 WHERE idPatient = ? AND statut IN ('annule', 'termine')");
            return $stmt->execute([$patientId]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
