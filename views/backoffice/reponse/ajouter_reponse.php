<?php
require_once '../../../config/config.php';
require_once '../../../models/Reclamation.php';
require_once '../../../models/Response.php';
require_once '../../../controllers/ReclamationController.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: admin_reclamations.php');
    exit;
}

// Fetch reclamation using Reclamation model
$reclamationModel = new Reclamation();

// Since findForUser requires user ID, we need to get the reclamation differently for admin
$pdo = (new config())->getConnexion();
$stmt = $pdo->prepare("SELECT * FROM reclamation WHERE id = ?");
$stmt->execute([$id]);
$reclamationData = $stmt->fetch();

if (!$reclamationData) {
    header('Location: admin_reclamations.php');
    exit;
}

// Create Reclamation object
$reclamation = new Reclamation();
$reclamation->hydrate($reclamationData);

// Get username
$stmt = $pdo->prepare("SELECT username FROM user WHERE id = ?");
$stmt->execute([$reclamation->getUserId()]);
$user = $stmt->fetch();
$username = $user['username'] ?? 'Utilisateur inconnu';

// Get errors from session if any
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

$pageTitle = "Répondre à la Réclamation";
?>

<?php include '../../../admin_sidebar.php'; ?>

<div class="admin-content">
    <!-- Header Section -->
    <div class="content-header" style="margin-bottom: 2rem;">
        <h1 style="color: #1e293b; font-size: 2rem; margin-bottom: 0.5rem;">Répondre à la Réclamation</h1>
        <p style="color: #64748b; font-size: 1.1rem;">Ajouter une réponse à cette réclamation</p>
    </div>

    <!-- Reclamation Details -->
    <div class="card" style="background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 1rem; color: #1f2937;"><?= htmlspecialchars($reclamation->getTitre()); ?></h3>
        <div style="margin-bottom: 1rem;">
            <span class="badge <?= $reclamation->getType() === 'urgence' ? 'badge-urgence' : 'badge-normal'; ?>" style="padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; margin-right: 0.5rem;">
                <i class="fas <?= $reclamation->getType() === 'urgence' ? 'fa-exclamation-triangle' : 'fa-file-alt'; ?>"></i>
                <?= htmlspecialchars($reclamation->getType()); ?>
            </span>
            <span class="badge statut-<?= str_replace(' ', '-', $reclamation->getStatut()); ?>" style="padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                <i class="fas 
                    <?= $reclamation->getStatut() === 'fermé' ? 'fa-check-circle' : 
                       ($reclamation->getStatut() === 'en cours' ? 'fa-spinner' : 'fa-clock'); ?>">
                </i>
                <?= htmlspecialchars($reclamation->getStatut()); ?>
            </span>
        </div>
        <p style="margin-bottom: 0.5rem;"><strong>Description :</strong></p>
        <p style="color: #4b5563; line-height: 1.5;"><?= nl2br(htmlspecialchars($reclamation->getDescription())); ?></p>
        <div style="margin-top: 1rem; color: #6b7280; font-size: 0.9rem;">
            <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($reclamation->getDate())); ?></p>
            <p><strong>Utilisateur :</strong> <?= htmlspecialchars($username); ?></p>
        </div>
    </div>

    <!-- Response Form -->
    <div class="card" style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <?php if (!empty($errors)): ?>
            <div class="alert-error" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="enregistrer_reponse.php">
            <input type="hidden" name="id_reclamation" value="<?= $reclamation->getId(); ?>">

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="contenu" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Votre réponse *</label>
                <textarea id="contenu" name="contenu" rows="5"
                          placeholder="Écrivez votre réponse à cette réclamation (minimum 5 caractères, maximum 3000)..."
                          style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 16px; font-family: inherit; resize: vertical;"></textarea>
                <div style="display: flex; justify-content: space-between; margin-top: 0.5rem;">
                    <span id="contenuError" style="color: #ef4444; font-size: 0.85rem; display: none;"></span>
                    <span id="contenuCount" style="color: #64748b; font-size: 0.85rem;">0 / 3000 caractères</span>
                </div>
            </div>

            <!-- Statut Information -->
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <div style="background: <?= $reclamation->getStatut() === 'ouvert' ? '#fef3c7' : ($reclamation->getStatut() === 'en cours' ? '#dbeafe' : '#dcfce7'); ?>; border: 1px solid <?= $reclamation->getStatut() === 'ouvert' ? '#fde68a' : ($reclamation->getStatut() === 'en cours' ? '#93c5fd' : '#86efac'); ?>; border-radius: 8px; padding: 1rem;">
                    <p style="margin: 0; color: <?= $reclamation->getStatut() === 'ouvert' ? '#92400e' : ($reclamation->getStatut() === 'en cours' ? '#1e40af' : '#166534'); ?>; font-size: 0.9rem; font-weight: 600;">
                        <i class="fas fa-info-circle"></i>
                        <strong>Changement de statut automatique :</strong>
                    </p>
                    <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem; color: <?= $reclamation->getStatut() === 'ouvert' ? '#92400e' : ($reclamation->getStatut() === 'en cours' ? '#1e40af' : '#166534'); ?>; font-size: 0.85rem;">
                        <?php if ($reclamation->getStatut() === 'ouvert'): ?>
                            <li>Statut actuel : <strong>Ouvert</strong></li>
                            <li>Après l'envoi de votre réponse, le statut passera automatiquement à <strong>"En cours"</strong></li>
                        <?php elseif ($reclamation->getStatut() === 'en cours'): ?>
                            <li>Statut actuel : <strong>En cours</strong></li>
                            <li>Après l'envoi de votre réponse, le statut passera automatiquement à <strong>"Fermé"</strong></li>
                        <?php else: ?>
                            <li>Statut actuel : <strong>Fermé</strong></li>
                            <li>Cette réclamation est déjà fermée. Le statut ne changera pas.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="form-actions" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-paper-plane"></i>
                    Envoyer la réponse
                </button>
                <a href="details_reclamation.php?id=<?= $reclamation->getId(); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<script>
const contenu = document.getElementById('contenu');
const contenuError = document.getElementById('contenuError');
const contenuCount = document.getElementById('contenuCount');

contenu.addEventListener('input', function() {
    const currentLength = this.value.length;
    contenuCount.textContent = currentLength + ' / 3000 caractères';
    
    if (currentLength > 2800) {
        contenuCount.style.color = '#ef4444';
    } else {
        contenuCount.style.color = '#64748b';
    }
    
    if (this.value.trim().length < 5) {
        contenuError.textContent = 'La réponse doit contenir au moins 5 caractères';
        contenuError.style.display = 'block';
    } else {
        contenuError.style.display = 'none';
    }
});

document.querySelector('form').addEventListener('submit', function(e) {
    const value = contenu.value.trim();
    if (value.length < 5) {
        e.preventDefault();
        contenuError.textContent = 'La réponse doit contenir au moins 5 caractères';
        contenuError.style.display = 'block';
        contenu.focus();
    }
});
</script>

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

.btn-secondary {
    background: #6b7280;
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
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<?php include '../../../admin_footer.php'; ?>