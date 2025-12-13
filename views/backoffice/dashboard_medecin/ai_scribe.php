<?php
require_once '../../../controllers/UserController.php';
require_once '../../../controllers/ChatController.php';
require_once '../../../controllers/ServiceController.php';

$userController = new UserController();
$userController->requireRole('medecin');
$currentUser = $userController->getUserById($_SESSION['user_id']);

// Fetch Specialty (Service Name)
$serviceController = new ServiceController();
$service = $serviceController->obtenirServiceParId($currentUser['idService']);
$specialty = $service ? $service['name'] : 'Médecine Générale';

$report = "";
$rawNotes = "";
$patientName = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $rawNotes = $_POST['notes'];
    $patientName = $_POST['patient_name'] ?? "Patient";

    // Context for Signature
    $doctorContext = [
        'name' => 'Dr. ' . $currentUser['prenom'] . ' ' . $currentUser['nom'],
        'specialty' => $specialty,
        'address' => $currentUser['adresse'] ?? 'Non renseignée',
        'date' => date('d/m/Y')
    ];

    $chatController = new ChatController();
    $report = $chatController->generateMedicalReport($rawNotes, $patientName, $doctorContext);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistant IA - Rédaction Médicale</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="../../css/style.css?v=<?php echo time(); ?>">
    <style>
        .scribe-container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            align-items: start;
        }

        .input-card,
        .output-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .output-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            position: sticky;
            top: 2rem;
        }

        h1 {
            color: #0f172a;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }

        .subtitle {
            color: #64748b;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #334155;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            font-family: inherit;
            resize: vertical;
            transition: border-color 0.2s;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-generate {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
        }

        .report-content {
            white-space: pre-wrap;
            font-family: 'Georgia', serif;
            line-height: 1.6;
            color: #1e293b;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            min-height: 300px;
        }

        .actions-row {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn-action {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-action:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .loading-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.8);
            z-index: 100;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e2e8f0;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @media (max-width: 900px) {
            .scribe-container {
                grid-template-columns: 1fr;
            }

            .output-card {
                position: static;
            }
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

<body style="background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif;">

    <?php include 'partials/header.php'; ?>

    <div class="loading-overlay" id="loader">
        <div class="spinner"></div>
        <h3 style="margin-top:1rem; color:#334155;">L'IA rédige votre rapport...</h3>
    </div>

    <main class="scribe-container">

        <!-- Input Section -->
        <div class="input-card">
            <h1>Assistant de Rédaction</h1>
            <p class="subtitle">Transformez vos notes brutes en comptes-rendus professionnels instantanément.</p>

            <form method="POST" id="scribeForm" onsubmit="document.getElementById('loader').style.display = 'flex'">
                <input type="hidden" name="generate" value="1">

                <div class="form-group">
                    <label>Nom du Patient</label>
                    <input type="text" name="patient_name" value="<?= htmlspecialchars($patientName) ?>"
                        placeholder="Ex: Jean Dupont">
                </div>

                <div class="form-group">
                    <label>Notes de consultation (Mots-clés, dictée, observations)</label>
                    <textarea name="notes" rows="12"
                        placeholder="Ex: 35 ans, toux sèche depuis 3j, fièvre 38.5 hier soir. Auscultation pulmonaire normale. Gorge un peu rouge. Angine virale probable. Repos + Doliprane + Miel. Arrêt travail 2j."><?= htmlspecialchars($rawNotes) ?></textarea>
                </div>

                <button type="submit" class="btn-generate">
                    <i class="fas fa-magic"></i> Générer le Compte-Rendu
                </button>
            </form>
        </div>

        <!-- Output Section -->
        <div class="output-card">
            <h2 style="margin-bottom:1rem; color:#1e293b;"><i class="fas fa-file-medical-alt"
                    style="color:#3b82f6;"></i> Résultat généré</h2>

            <?php if ($report): ?>
                <div class="report-content" id="reportText"><?= htmlspecialchars($report) ?></div>

                <div class="actions-row">
                    <button class="btn-action" onclick="copyToClipboard()">
                        <i class="far fa-copy"></i> Copier
                    </button>
                    <button class="btn-action" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimer
                    </button>
                </div>
            <?php else: ?>
                <div
                    style="text-align:center; padding:3rem; color:#94a3b8; border: 2px dashed #cbd5e1; border-radius:12px;">
                    <i class="fas fa-robot" style="font-size:3rem; margin-bottom:1rem; opacity:0.5;"></i>
                    <p>Le compte-rendu s'affichera ici après génération.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function copyToClipboard() {
            const text = document.getElementById('reportText').innerText;
            navigator.clipboard.writeText(text).then(() => {
                const btn = document.querySelector('.btn-action');
                const uniqueOriginalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copié !';
                setTimeout(() => {
                    btn.innerHTML = uniqueOriginalHTML;
                }, 2000);
            });
        }
    </script>
</body>

</html>