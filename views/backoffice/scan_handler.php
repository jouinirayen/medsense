<?php
session_start();
require_once '../../controllers/ReservationController.php';

$id = $_GET['id'] ?? null;
$token = $_GET['token'] ?? null;
$message = '';
$status = 'error'; // success, error

// 1. Check Authentication
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page with return URL
    $currentUrl = urlencode($_SERVER['REQUEST_URI']);
    header("Location: ../../views/frontoffice/login.php?redirect=" . $currentUrl);
    exit;
}

// 2. Check Role (Must be Doctor)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'medecin') {
    die("Accès refusé. Vous devez être connecté en tant que médecin.");
}

if ($id && $token) {
    // Validate Token
    $expectedToken = hash('sha256', $id . 'MedsenseSecret');

    if ($token === $expectedToken) {
        $controller = new ReservationController();
        if ($controller->updateStatus($id, 'termine')) {
            $status = 'success';
            $message = 'Rendez-vous marqué comme terminé avec succès.';
        } else {
            $message = 'Erreur lors de la mise à jour du statut.';
        }
    } else {
        $message = 'Token de sécurité invalide.';
    }
} else {
    $message = 'Paramètres manquants.';
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation Rendez-vous</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
        }

        .success {
            background: #dcfce7;
            color: #16a34a;
        }

        .error {
            background: #fee2e2;
            color: #dc2626;
        }

        h1 {
            margin: 0 0 0.5rem;
            font-size: 1.5rem;
            color: #0f172a;
        }

        p {
            color: #64748b;
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #0ea5e9;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="card">
        <?php if ($status === 'success'): ?>
            <div class="icon-circle success"><i class="fas fa-check"></i></div>
            <h1>Bip ! Validé</h1>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php else: ?>
            <div class="icon-circle error"><i class="fas fa-times"></i></div>
            <h1>Erreur</h1>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <a href="dashboard.php" class="btn">Retour au tableau de bord</a>
    </div>
</body>

</html>