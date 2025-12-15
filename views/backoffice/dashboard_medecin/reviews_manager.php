<?php
require_once '../../../controllers/UserController.php';
require_once '../../../controllers/ReservationController.php';
require_once '../../../controllers/ChatController.php';

$userController = new UserController();
$userController->requireRole('medecin');
$currentUser = $userController->getUserById($_SESSION['user_id']);
$doctorName = $currentUser['prenom'] . ' ' . $currentUser['nom'];

$reservationController = new ReservationController();
// Fetch reviews (Appointments with rating)
// We might need to add a specialized method in ReservationController or do a custom query here
// For now, let's use a custom query for speed as ReservationController might not have 'getReviews'
$db = (new config())->getConnexion();
$stmt = $db->prepare("SELECT r.*, u.nom as patientNom, u.prenom as patientPrenom 
                      FROM rendezvous r 
                      JOIN utilisateur u ON r.idPatient = u.id_utilisateur 
                      WHERE r.idMedecin = ? AND r.note IS NOT NULL 
                      ORDER BY r.date DESC");
$stmt->execute([$_SESSION['user_id']]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle AJAX AI Generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'generate_response') {
        $chatController = new ChatController();
        $response = $chatController->generateReviewResponse(
            $_POST['patient_name'],
            $_POST['rating'],
            $_POST['comment'],
            $doctorName
        );
        echo $response;
        exit;
    }
    // Handle Save Response
    if ($_POST['action'] === 'save_response') {
        $idRDV = $_POST['id_rdv'];
        $reply = $_POST['reply'];
        $stmtUpdate = $db->prepare("UPDATE rendezvous SET reponse_medecin = ? WHERE idRDV = ?");
        $stmtUpdate->execute([$reply, $idRDV]);
        header('Location: reviews_manager.php?success=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Avis & Réponses</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="../../css/style.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .page-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }

        .review-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .patient-info {
            font-weight: 600;
            color: #0f172a;
        }

        .stars {
            color: #f59e0b;
        }

        .comment-text {
            color: #475569;
            margin-bottom: 1.5rem;
            line-height: 1.6;
            font-style: italic;
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
        }

        .response-section {
            border-top: 1px solid #e2e8f0;
            padding-top: 1rem;
        }

        .ai-btn {
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .save-btn {
            background: #0ea5e9;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
        }

        textarea {
            width: 100%;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 12px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.95rem;
            line-height: 1.6;
            color: #334155;
            background-color: #f8fafc;
            resize: vertical;
            min-height: 100px;
            transition: all 0.2s ease;
            box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);
        }

        textarea:focus {
            outline: none;
            border-color: #3b82f6;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background: white;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .nav-links {
            display: flex;
            gap: 1rem;
        }

        .nav-link {
            text-decoration: none;
            color: #64748b;
            font-weight: 500;
        }

        .nav-link.active {
            color: #0ea5e9;
        }
    </style>
</head>

<body style="background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif;">

    <?php include 'partials/header.php'; ?>

    <main class="page-container">
        <h1 style="margin-bottom: 2rem; color: #0f172a;">Gestion des Avis Patients</h1>

        <?php if (empty($reviews)): ?>
            <div style="text-align: center; padding: 4rem; color: #94a3b8;">
                <i class="far fa-comment-dots" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p>Aucun avis reçu pour le moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $r): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div class="patient-info">
                            <?= htmlspecialchars($r['patientPrenom'] . ' ' . $r['patientNom']) ?>
                            <span style="font-weight: 400; color: #94a3b8; font-size: 0.9rem;">•
                                <?= date('d/m/Y', strtotime($r['date'])) ?></span>
                        </div>
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="<?= ($i <= $r['note']) ? 'fas' : 'far' ?> fa-star"></i>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="comment-text">
                        <?= !empty($r['commentaire']) ? htmlspecialchars($r['commentaire']) : "Pas de commentaire écrit." ?>
                    </div>

                    <div class="response-section">
                        <?php if (!empty($r['reponse_medecin'])): ?>
                            <div style="background: #eff6ff; padding: 1rem; border-radius: 8px; border: 1px solid #dbeafe;">
                                <strong style="color: #1e40af; font-size: 0.9rem;">Votre réponse :</strong>
                                <p style="margin-top: 5px; color: #1e3a8a;"><?= htmlspecialchars($r['reponse_medecin']) ?></p>
                            </div>
                        <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="save_response">
                                <input type="hidden" name="id_rdv" value="<?= $r['idRDV'] ?>">

                                <button type="button" class="ai-btn"
                                    onclick="generateReply(this, '<?= htmlspecialchars($r['patientPrenom']) ?>', <?= $r['note'] ?>, `<?= addslashes($r['commentaire']) ?>`)">
                                    <i class="fas fa-magic"></i> Suggérer une réponse IA
                                </button>

                                <textarea name="reply" rows="3" placeholder="Écrivez votre réponse ici..."></textarea>

                                <div style="text-align: right;">
                                    <button type="submit" class="save-btn">Publier la réponse</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <script>
        function generateReply(btn, patientName, rating, comment) {
            const textarea = btn.closest('form').querySelector('textarea');
            const originalText = btn.innerHTML;

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'generate_response');
            formData.append('patient_name', patientName);
            formData.append('rating', rating);
            formData.append('comment', comment || "Pas de commentaire");

            fetch('reviews_manager.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(text => {
                    let i = 0;
                    textarea.value = "";
                    const typeWriter = () => {
                        if (i < text.length) {
                            textarea.value += text.charAt(i);
                            i++;
                            setTimeout(typeWriter, 10); // Typing effect
                        }
                    };
                    typeWriter();
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                })
                .catch(err => {
                    console.error(err);
                    btn.innerHTML = 'Erreur';
                });
        }
    </script>
</body>

</html>