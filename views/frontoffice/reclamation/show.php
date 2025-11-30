<?php
require_once '../../../config/config.php';
require_once '../../../models/Reclamation.php';
require_once '../../../models/Response.php';

 
// Get reclamation ID from URL
$id = $_GET['id'] ?? null;
$userId = 1; // Hardcoded user ID

if (!$id) {
    header('Location: index.php');
    exit;
}

// Fetch reclamation and responses
$reclamationModel = new Reclamation();
$responseModel = new Response();

$reclamation = $reclamationModel->findForUser($id, $userId);
$responses = $responseModel->forReclamation($id);

if (!$reclamation) {
    header('Location: index.php');
    exit;
}

$pageTitle = "Détails de la Réclamation";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body>
<?php include '../../../navbar.php'; ?>
<main class="main-content">
    <div class="container">
        <div class="header-section">
            <a href="index.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i>
                Retour à la liste
            </a>
            <h1>Détails de la Réclamation</h1>
        </div>

        <div class="card reclamation-details">
            <div class="reclamation-header">
                <h2><?= htmlspecialchars($reclamation['titre']); ?></h2>
                <div class="reclamation-meta">
                    <span class="badge badge-<?= $reclamation['type'] === 'urgence' ? 'urgence' : 'normal'; ?>">
                        <?= htmlspecialchars($reclamation['type']); ?>
                    </span>
                    <span class="badge badge-statut statut-<?= str_replace(' ', '-', $reclamation['statut']); ?>">
                        <?= htmlspecialchars($reclamation['statut']); ?>
                    </span>
                    <span class="date">
                        <i class="far fa-calendar"></i>
                        <?= date('d/m/Y H:i', strtotime($reclamation['date'])); ?>
                    </span>
                </div>
            </div>

            <div class="reclamation-content">
                <h3>Description</h3>
                <div class="description-box">
                    <?= nl2br(htmlspecialchars($reclamation['description'])); ?>
                </div>
            </div>
        </div>

        <div class="responses-section">
            <div class="responses-header">
                <h3>
                    <i class="fas fa-comments"></i>
                    Réponses (<?= count($responses); ?>)
                </h3>
            </div>

            <?php if ($responses): ?>
                <div class="responses-list">
                    <?php foreach ($responses as $res): ?>
                        <div class="response-card">
                            <div class="response-header">
                                <div class="response-author">
                                    <i class="fas fa-user"></i>
                                    <?= htmlspecialchars($res['username'] ?? 'Administrateur'); ?>
                                </div>
                                <div class="response-date">
                                    <i class="far fa-clock"></i>
                                    <?= date('d/m/Y H:i', strtotime($res['date'])); ?>
                                </div>
                            </div>
                            <div class="response-content">
                                <?= nl2br(htmlspecialchars($res['contenu'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-comment-slash"></i>
                    <h4>Aucune réponse pour cette réclamation</h4>
                    <p>Les réponses de l'administration apparaîtront ici.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
    .container {
        max-width: 900px;
        margin: 0 auto;
    }

    .header-section {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .header-section h1 {
        color: #1f2937;
        font-size: 2rem;
        margin: 0;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 10px 20px;
        background: #6b7280;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background: #4b5563;
        transform: translateY(-2px);
    }

    .reclamation-details {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        margin-bottom: 2rem;
        border-left: 4px solid #3b82f6;
    }

    .reclamation-header {
        display: flex;
        justify-content: between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .reclamation-header h2 {
        color: #1f2937;
        font-size: 1.5rem;
        margin: 0;
        flex: 1;
        min-width: 300px;
    }

    .reclamation-meta {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-normal {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-urgence {
        background: #fecaca;
        color: #dc2626;
    }

    .badge-statut {
        background: #dcfce7;
        color: #166534;
    }

    .statut-ouvert {
        background: #fef3c7;
        color: #92400e;
    }

    .statut-en-cours {
        background: #dbeafe;
        color: #1e40af;
    }

    .statut-fermé {
        background: #dcfce7;
        color: #166534;
    }

    .date {
        color: #6b7280;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .reclamation-content h3 {
        color: #374151;
        margin-bottom: 1rem;
        font-size: 1.2rem;
    }

    .description-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1.5rem;
        line-height: 1.6;
        color: #4b5563;
    }

    .responses-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 2rem;
    }

    .responses-header {
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
    }

    .responses-header h3 {
        color: #1f2937;
        font-size: 1.3rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .responses-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .response-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1.5rem;
        transition: all 0.3s ease;
    }

    .response-card:hover {
        border-color: #3b82f6;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .response-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .response-author {
        font-weight: 600;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .response-date {
        color: #6b7280;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .response-content {
        line-height: 1.6;
        color: #4b5563;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state h4 {
        font-size: 1.2rem;
        margin-bottom: 0.5rem;
        color: #374151;
    }

    @media (max-width: 768px) {
        .header-section {
            flex-direction: column;
            align-items: flex-start;
        }

        .header-section h1 {
            font-size: 1.5rem;
        }

        .reclamation-header {
            flex-direction: column;
        }

        .reclamation-header h2 {
            min-width: auto;
        }

        .reclamation-meta {
            justify-content: flex-start;
        }

        .response-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding: 0 1rem;
        }

        .reclamation-details,
        .responses-section {
            padding: 1rem;
        }

        .badge {
            font-size: 0.7rem;
            padding: 4px 8px;
        }
    }
</style>

<?php include '../../../footer.php'; ?>