<?php
require_once '../../../controllers/ReservationController.php';
require_once '../../../controllers/UserController.php';

$userController = new UserController();
$userController->requireRole('medecin');

$userId = $_SESSION['user_id'];
$reservationController = new ReservationController();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_availability'])) {
        $date = $_POST['date'];
        $type = $_POST['type']; // 'full' or 'partial'
        $type = $_POST['type']; // 'full' or 'partial'

        $start = null;
        $end = null;

        if ($type === 'partial') {
            $start = $_POST['start_time'];
            $end = $_POST['end_time'];
        }

        if ($reservationController->addUnavailability($userId, $date, $start, $end)) {
            $success = "Indisponibilité ajoutée avec succès.";
        } else {
            $error = "Erreur lors de l'ajout.";
        }
    } elseif (isset($_POST['delete_id'])) {
        if ($reservationController->deleteUnavailability($_POST['delete_id'])) {
            $success = "Indisponibilité supprimée.";
        } else {
            $error = "Erreur lors de la suppression.";
        }
    }
}

$unavailabilities = $reservationController->getUnavailabilities($userId);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer mes disponibilités</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="../../css/style.css?v=<?php echo time(); ?>">
    <style>
        .availability-container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .cards-wrapper {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }

        .form-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }

        .list-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #1e293b;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-family: inherit;
        }

        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            background: #0ea5e9;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-submit:hover {
            background: #0284c7;
        }

        .unavail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .unavail-item:last-child {
            border-bottom: none;
        }

        .badge-full {
            background: #fee2e2;
            color: #ef4444;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-partial {
            background: #fef3c7;
            color: #d97706;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .delete-btn {
            color: #ef4444;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
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

    <main class="availability-container">
        <h1 style="margin-bottom: 2rem; font-size: 1.8rem; color: #0f172a;">Gérer mes absences</h1>

        <?php if (isset($success)): ?>
            <div style="background:#dcfce7; color:#166534; padding:1rem; border-radius:8px; margin-bottom:1rem;">
                <?= $success ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div style="background:#fee2e2; color:#991b1b; padding:1rem; border-radius:8px; margin-bottom:1rem;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="cards-wrapper">
            <!-- Form Card -->
            <div class="form-card">
                <h2 style="margin-bottom:1.5rem; font-size:1.2rem;">Ajouter une indisponibilité</h2>
                <form method="POST">
                    <input type="hidden" name="add_availability" value="1">

                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date" required class="form-control" min="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="form-group">
                        <label>Type d'absence</label>
                        <select name="type" id="typeSelect" class="form-control" onchange="toggleTimeInputs()">
                            <option value="full">Journée entière</option>
                            <option value="partial">Créneau horaire</option>
                        </select>
                    </div>

                    <div id="timeInputs" style="display:none;">
                        <div class="form-group">
                            <label>Heure de début</label>
                            <input type="time" name="start_time" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Heure de fin</label>
                            <input type="time" name="end_time" class="form-control">
                        </div>
                    </div>



                    <button type="submit" class="btn-submit">Ajouter</button>
                </form>
            </div>

            <!-- List Card -->
            <div class="list-card">
                <h2 style="margin-bottom:1.5rem; font-size:1.2rem;">Mes indisponibilités à venir</h2>
                <?php if (empty($unavailabilities)): ?>
                    <p style="color:#94a3b8; text-align:center;">Aucune absence programmée.</p>
                <?php else: ?>
                    <div class="unavail-list">
                        <?php foreach ($unavailabilities as $u): ?>
                            <div class="unavail-item">
                                <div>
                                    <div style="font-weight:600; color:#0f172a; margin-bottom:4px;">
                                        <?= date('d/m/Y', strtotime($u['date'])) ?>
                                        <?php if ($u['heure_debut']): ?>
                                            <span style="font-weight:400; color:#64748b; font-size:0.9em;">
                                                (<?= substr($u['heure_debut'], 0, 5) ?> - <?= substr($u['heure_fin'], 0, 5) ?>)
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                </div>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <?php if ($u['heure_debut']): ?>
                                        <span class="badge-partial">Partiel</span>
                                    <?php else: ?>
                                        <span class="badge-full">Journée</span>
                                    <?php endif; ?>

                                    <form method="POST" onsubmit="return confirm('Supprimer cette absence ?');">
                                        <input type="hidden" name="delete_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="delete-btn"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        function toggleTimeInputs() {
            const type = document.getElementById('typeSelect').value;
            const timeInputs = document.getElementById('timeInputs');
            if (type === 'partial') {
                timeInputs.style.display = 'block';
                timeInputs.querySelectorAll('input').forEach(i => i.required = true);
            } else {
                timeInputs.style.display = 'none';
                timeInputs.querySelectorAll('input').forEach(i => i.required = false);
            }
        }
    </script>
</body>

</html>