<?php
require_once '../../../config/config.php';
require_once '../../../models/Reclamation.php';
require_once '../../../models/Response.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: admin_reclamations.php');
    exit;
}

// Fetch reclamation using direct database connection
$pdo = (new config())->getConnexion();
$stmt = $pdo->prepare("SELECT r.*, u.username FROM reclamation r LEFT JOIN user u ON r.id_user = u.id WHERE r.id = ?");
$stmt->execute([$id]);
$reclamation = $stmt->fetch();

if (!$reclamation) {
    header('Location: admin_reclamations.php');
    exit;
}

// Fetch responses using model
$responseModel = new Response();
$responses = $responseModel->forReclamation($id);

$pageTitle = "Détails Réclamation #" . $reclamation['id'];
?>

<?php include '../../../admin_sidebar.php'; ?>

<div class="admin-content">
    <!-- Header Section -->
    <div class="content-header" style="margin-bottom: 2rem;">
        <h1 style="color: #1e293b; font-size: 2rem; margin-bottom: 0.5rem;">Détails de la Réclamation</h1>
        <p style="color: #64748b; font-size: 1.1rem;">Informations complètes et réponses</p>
    </div>

    <!-- Reclamation Details -->
    <div class="card" style="background: white; padding: 2rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid <?= $reclamation['type'] === 'urgence' ? '#ef4444' : '#3b82f6'; ?>">
        <div class="reclamation-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <h2 style="margin: 0; color: #1f2937; flex: 1;"><?= htmlspecialchars($reclamation['titre']); ?></h2>
            <div class="reclamation-meta" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <span class="badge <?= $reclamation['type'] === 'urgence' ? 'badge-urgence' : 'badge-normal'; ?>" style="padding: 6px 12px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                    <i class="fas <?= $reclamation['type'] === 'urgence' ? 'fa-exclamation-triangle' : 'fa-file-alt'; ?>"></i>
                    <?= htmlspecialchars($reclamation['type']); ?>
                </span>
                <span class="badge statut-<?= str_replace(' ', '-', $reclamation['statut']); ?>" style="padding: 6px 12px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                    <i class="fas 
                        <?= $reclamation['statut'] === 'fermé' ? 'fa-check-circle' : 
                           ($reclamation['statut'] === 'en cours' ? 'fa-spinner' : 'fa-clock'); ?>">
                    </i>
                    <?= htmlspecialchars($reclamation['statut']); ?>
                </span>
            </div>
        </div>

        <div class="reclamation-content" style="margin-bottom: 1.5rem;">
            <h3 style="color: #374151; margin-bottom: 0.5rem;">Description</h3>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; line-height: 1.6; color: #4b5563;">
                <?= nl2br(htmlspecialchars($reclamation['description'])); ?>
            </div>
        </div>

        <div class="reclamation-info" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; color: #6b7280; font-size: 0.9rem;">
            <div>
                <strong><i class="far fa-calendar"></i> Date :</strong><br>
                <?= date('d/m/Y H:i', strtotime($reclamation['date'])); ?>
            </div>
            <div>
                <strong><i class="fas fa-user"></i> Utilisateur :</strong><br>
                <?= htmlspecialchars($reclamation['username'] ?? 'Utilisateur inconnu'); ?>
            </div>
            <div>
                <strong><i class="fas fa-hashtag"></i> ID Réclamation :</strong><br>
                #<?= $reclamation['id']; ?>
            </div>
            <div>
                <strong><i class="far fa-comments"></i> Réponses :</strong><br>
                <?= count($responses); ?>
            </div>
        </div>

        <div class="reclamation-actions" style="display: flex; gap: 1rem; margin-top: 2rem; flex-wrap: wrap;">
            <a href="ajouter_reponse.php?id=<?= $reclamation['id']; ?>" class="btn btn-success">
                <i class="fas fa-reply"></i>
                Répondre
            </a>
            <a href="admin_supprimer_reclamation.php?id=<?= $reclamation['id']; ?>" class="btn btn-danger" 
               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réclamation ? Cette action est irréversible.')">
                <i class="fas fa-trash"></i>
                Supprimer
            </a>
        </div>
    </div>

    <!-- Responses Section -->
    <div class="responses-section" style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="responses-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <h2 style="margin: 0; color: #1f2937;">
                <i class="fas fa-comments"></i>
                Réponses (<?= count($responses); ?>)
            </h2>
        </div>

        <?php if (empty($responses)): ?>
            <div class="empty-state" style="text-align: center; padding: 2rem; color: #6b7280;">
                <i class="fas fa-comment-slash" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <h3 style="color: #374151; margin-bottom: 0.5rem;">Aucune réponse</h3>
                <p>Soyez le premier à répondre à cette réclamation.</p>
            </div>
        <?php else: ?>
            <div class="responses-list" style="display: flex; flex-direction: column; gap: 1.5rem;">
                <?php foreach ($responses as $res): ?>
                    <div class="response-card" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; transition: all 0.3s ease;">
                        <div class="response-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                            <div class="response-author" style="font-weight: 600; color: #374151;">
                                <i class="fas fa-user"></i>
                                <?= htmlspecialchars($res['username'] ?? 'Administrateur'); ?>
                            </div>
                            <div class="response-date" style="color: #6b7280; font-size: 0.9rem;">
                                <i class="far fa-clock"></i>
                                <?= date('d/m/Y H:i', strtotime($res['date'])); ?>
                            </div>
                        </div>
                        <div class="response-content" style="line-height: 1.6; color: #4b5563;">
                            <?= nl2br(htmlspecialchars($res['contenu'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Button Styles */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 0.9rem;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

/* Badge Styles */
.badge-normal {
    background: #dbeafe;
    color: #1e40af;
}

.badge-urgence {
    background: #fecaca;
    color: #dc2626;
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

/* Responsive Design */
@media (max-width: 768px) {
    .reclamation-header {
        flex-direction: column;
    }
    
    .reclamation-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
    
    .reclamation-info {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../../../admin_footer.php'; ?>