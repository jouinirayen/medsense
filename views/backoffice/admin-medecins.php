<?php

session_start([
    'cookie_secure' => false,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../frontoffice/auth/sign-in.php');
    exit;
}

require_once __DIR__ . '/../../controllers/AdminController.php';

$adminController = new AdminController();
$action = '';
$doctorId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING) ?? '';
    $doctorId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?? '';
    $doctorId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $_SESSION['error_message'] = "Token de sécurité invalide.";
        header('Location: admin-medecins.php');
        exit;
    }
    
    switch ($action) {
        case 'approve':
        case 'reject':
        case 'suspend':
            if ($doctorId) {
                $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING) ?? '';
                $result = $adminController->handleApprovalRequest([
                    'action' => $action,
                    'doctor_id' => $doctorId,
                    'reason' => $reason
                ]);
                
                if ($result['success']) {
                    $_SESSION['success_message'] = $result['message'];
                } else {
                    $_SESSION['error_message'] = $result['message'];
                }
                header('Location: admin-medecins.php');
                exit;
            }
            break;
            
        case 'verify_diploma':
            if ($doctorId) {
                $diploma_status = filter_input(INPUT_POST, 'diploma_status', FILTER_SANITIZE_STRING);
                $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING) ?? '';
                
                if ($diploma_status) {
                    $result = $adminController->verifyDiploma(
                        $doctorId, 
                        $diploma_status,
                        $comment
                    );
                    if ($result['success']) {
                        $_SESSION['success_message'] = $result['message'];
                    } else {
                        $_SESSION['error_message'] = $result['message'];
                    }
                }
                header('Location: admin-medecins.php');
                exit;
            }
            break;
    }
}

function handleResultMessage($result) {
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    switch ($action) {
        case 'delete':
            if ($doctorId) {
                $result = $adminController->deactivateUser($doctorId);
                handleResultMessage($result);
                header('Location: admin-medecins.php');
                exit;
            }
            break;
            
        case 'activate':
            if ($doctorId) {
                $result = $adminController->activateUser($doctorId);
                handleResultMessage($result);
                header('Location: admin-medecins.php');
                exit;
            }
            break;
            
        case 'permanent_delete':
            if ($doctorId) {
                $result = $adminController->permanentlyDeleteUser($doctorId);
                handleResultMessage($result);
                header('Location: admin-medecins.php');
                exit;
            }
            break;
            
        case 'export_excel':
            $adminController->exportDoctorsToExcel();
            exit;
    }
}

$filters = [];
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
$statut = filter_input(INPUT_GET, 'statut', FILTER_SANITIZE_STRING);

if (!empty($search)) {
    $filters['search'] = $search;
}
if (!empty($statut) && in_array($statut, ['actif', 'inactif', 'en_attente', 'rejeté', 'suspendu'])) {
    $filters['statut'] = $statut;
}

$doctorsResult = $adminController->getAllDoctors($filters);
$doctors = $doctorsResult['doctors'] ?? [];
$totalDoctors = $doctorsResult['count'] ?? 0;

$statsResult = $adminController->getApprovalStats();
$statusStats = $statsResult['status_stats'] ?? [];
$weeklyApproved = $statsResult['weekly_approved'] ?? 0;

$pendingResult = $adminController->getPendingDoctors();
$pendingDoctors = $pendingResult['doctors'] ?? [];
$pendingCount = $pendingResult['count'] ?? 0;
$statutCounts = [
    'actif' => 0,
    'inactif' => 0,
    'en_attente' => 0,
    'rejeté' => 0,
    'suspendu' => 0
];

foreach ($statusStats as $stat) {
    if (isset($statutCounts[$stat['statut']])) {
        $statutCounts[$stat['statut']] = (int)$stat['count'];
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Médecins - Medsense Medical</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <!-- Shared CSS -->
    <link rel="stylesheet" href="../frontoffice/page-accueil/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../backoffice/dashboard_service/css/dashboard.css?v=<?php echo time(); ?>">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    
    <style>
        /* Minimal local overrides */
        .dashboard-user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #3b82f6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .doctors-action-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .doctors-action-btn:hover {
            background: #f3f4f6;
        }
        .doctors-action-btn.success { color: #10b981; }
        .doctors-action-btn.danger { color: #ef4444; }
        .doctors-action-btn.warning { color: #f59e0b; }
        .doctors-action-btn.info { color: #3b82f6; }
        .doctors-action-btn.secondary { color: #6b7280; }
        
        .role-actif { background: rgba(16, 185, 129, 0.1); color: #166534; }
        .role-inactif { background: rgba(107, 114, 128, 0.1); color: #374151; }
        .role-en_attente { background: rgba(245, 158, 11, 0.1); color: #92400e; }
        .role-rejeté { background: rgba(239, 68, 68, 0.1); color: #991b1b; }
        .role-suspendu { background: rgba(239, 68, 68, 0.1); color: #991b1b; }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo-section">
            <img src="../images/logo.jpeg" alt="Logo Medsense" style="height: 50px; width: auto;">
        </div>
        <nav class="nav-links">
            <a href="../backoffice/admin_hub.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-th-large"></i></span>
                <span>Hub Central</span>
            </a>
            <a href="admin-users.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-users"></i></span>
                <span>Utilisateurs</span>
            </a>
            <a href="admin-medecins.php" class="nav-link active">
                <span class="nav-icon"><i class="fas fa-user-md"></i></span>
                <span>Médecins</span>
            </a>
            <a href="admin-reports-statistics.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-chart-line"></i></span>
                <span>Stats</span>
            </a>
            <a href="../backoffice/dashboard_service/dashboard.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-clipboard-list"></i></span>
                <span>Services</span>
            </a>
            <a href="../../../projet_unifie/views/back-office/dashboard.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-newspaper"></i></span>
                <span>Blog</span>
            </a>
            <a href="../../../projet_unifie/views/backoffice/reponse/admin_reclamations.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <span>Réclamations</span>
            </a>
                <a href="../../controllers/logout.php" class="nav-link logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
        </nav>
    </header>

    <main class="dashboard-container">
        <!-- Hero Section -->
        <section class="hero-section text-start mb-4">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 class="hero-title" style="font-size: 1.75rem; text-align: left;">Gestion des Médecins</h1>
                    <p class="hero-description" style="text-align: left;">
                        Gérez les inscriptions et les validations des médecins.
                    </p>
                </div>
                <button type="button" class="dashboard-btn btn-success" onclick="exportToExcel()" id="exportBtn">
                    <i class="fas fa-file-excel me-2"></i> Exporter
                    <span class="spinner-border spinner-border-sm d-none" role="status" id="exportSpinner"></span>
                </button>
            </div>
        </section>

        <?php if ($success_message): ?>
            <div style="padding: 1rem; background: #dcfce7; color: #166534; border-radius: 12px; margin-bottom: 2rem; border: 1px solid #bbf7d0;">
                <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success_message) ?>
                <button type="button" class="dashboard-alert-close" style="float: right;">&times;</button>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div style="padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 12px; margin-bottom: 2rem; border: 1px solid #fecaca;">
                <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error_message) ?>
                <button type="button" class="dashboard-alert-close" style="float: right;">&times;</button>
            </div>
        <?php endif; ?>

        <?php if ($pendingCount > 0): ?>
            <div style="padding: 1rem; background: #fffbeb; color: #92400e; border-radius: 12px; margin-bottom: 2rem; border: 1px solid #fcd34d;">
                <i class="fas fa-clock me-2"></i> <strong><?= $pendingCount ?> médecin(s)</strong> en attente d'approbation.
            </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="stats-grid mb-4">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Médecins</h3>
                    <span class="stat-value"><?= $totalDoctors ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3>Actifs</h3>
                    <span class="stat-value"><?= $statutCounts['actif'] ?></span>
                </div>
            </div>
             <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>En Attente</h3>
                    <span class="stat-value"><?= $pendingCount ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-info">
                    <h3>Rejetés</h3>
                    <span class="stat-value"><?= $statutCounts['rejeté'] ?></span>
                </div>
            </div>
             <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-calendar-week"></i>
                </div>
                <div class="stat-info">
                    <h3>Approuvés cette semaine</h3>
                    <span class="stat-value"><?= $weeklyApproved ?></span>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="dashboard-card mb-4" style="border-left: 4px solid #3b82f6;">
            <div class="dashboard-card-header">
                <h3 class="dashboard-card-title"><i class="fas fa-filter me-2"></i>Filtres</h3>
            </div>
            <div class="dashboard-card-body">
                <form method="GET" class="row g-3" id="filterForm" style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem;">
                    <div>
                        <input type="text" name="search" class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;"
                               placeholder="Rechercher (nom, prénom, email...)" 
                               value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                    <div>
                        <select name="statut" class="form-select" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                            <option value="">Tous les statuts</option>
                            <option value="actif" <?= ($statut ?? '') == 'actif' ? 'selected' : '' ?>>Actif</option>
                            <option value="inactif" <?= ($statut ?? '') == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                            <option value="en_attente" <?= ($statut ?? '') == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                            <option value="rejeté" <?= ($statut ?? '') == 'rejeté' ? 'selected' : '' ?>>Rejeté</option>
                            <option value="suspendu" <?= ($statut ?? '') == 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="dashboard-btn btn-primary" style="width: 100%; justify-content: center;">
                            <i class="fas fa-search me-1"></i> Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Doctors Table -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h3 class="dashboard-card-title">
                    <i class="fas fa-list me-2"></i> Liste des médecins
                    <span style="background: #eff6ff; color: #3b82f6; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; margin-left: 8px;"><?= $totalDoctors ?></span>
                </h3>
            </div>
            <div class="dashboard-card-body p-0">
                <div class="dashboard-table-responsive">
                    <table class="dashboard-table" id="doctorsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Médecin</th>
                                <th>Email</th>
                                <th>Spécialité</th>
                                <th>Date Inscription</th>
                                <th>Diplôme</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doctors as $doctor): ?>
                                <?php
                                $initials = strtoupper(substr($doctor['prenom'] ?? '', 0, 1) . substr($doctor['nom'] ?? '', 0, 1));
                                $statusClass = $doctor['statut'] ?? 'inactif';
                                $diplomeStatutClass = match($doctor['diplome_statut'] ?? '') {
                                    'validé' => 'success',
                                    'en attente' => 'warning',
                                    'rejeté' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <tr>
                                    <td>#<?= htmlspecialchars($doctor['id_utilisateur']) ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div class="dashboard-user-avatar">
                                                <?= htmlspecialchars($initials) ?>
                                            </div>
                                            <div>
                                                <div style="font-weight: 500; color: #111827;"><?= htmlspecialchars($doctor['nom'] . ' ' . $doctor['prenom']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($doctor['email']) ?></td>
                                    <td><?= htmlspecialchars($doctor['specialite'] ?? 'Non spécifiée') ?></td>
                                    <td><?= date('d/m/Y', strtotime($doctor['date_inscription'])) ?></td>
                                    <td>
                                        <?php if (!empty($doctor['diplome_path'])): ?>
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <a href="../<?= htmlspecialchars($doctor['diplome_path']) ?>" target="_blank" title="Voir" style="color: #3b82f6;"><i class="fas fa-eye"></i></a>
                                                <span class="dashboard-badge role-<?= $diplomeStatutClass ?>"><?= $doctor['diplome_statut'] ?? 'Non vérifié' ?></span>
                                                <button type="button" class="doctors-action-btn info" onclick="showVerifyDiplomaModal(<?= $doctor['id_utilisateur'] ?>)" title="Vérifier"><i class="fas fa-check-circle"></i></button>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: #9ca3af; font-size: 0.875rem;">Non fourni</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="dashboard-badge role-<?= $statusClass ?>">
                                            <?= ucfirst($doctor['statut']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <?php if ($doctor['statut'] === 'en_attente'): ?>
                                                <button type="button" class="doctors-action-btn success" onclick="approveDoctor(<?= $doctor['id_utilisateur'] ?>)" title="Approuver"><i class="fas fa-check"></i></button>
                                                <button type="button" class="doctors-action-btn danger" onclick="rejectDoctor(<?= $doctor['id_utilisateur'] ?>)" title="Rejeter"><i class="fas fa-times"></i></button>
                                            <?php endif; ?>
                                            
                                            <?php if ($doctor['statut'] === 'actif'): ?>
                                                <button type="button" class="doctors-action-btn warning" onclick="suspendDoctor(<?= $doctor['id_utilisateur'] ?>)" title="Suspendre"><i class="fas fa-pause"></i></button>
                                            <?php endif; ?>
                                            
                                            <?php if (in_array($doctor['statut'], ['inactif', 'suspendu'])): ?>
                                                <button type="button" class="doctors-action-btn success" onclick="activateUser(<?= $doctor['id_utilisateur'] ?>)" title="Activer"><i class="fas fa-power-off"></i></button>
                                            <?php endif; ?>
                                            
                                            <?php if ($doctor['statut'] !== 'inactif'): ?>
                                                <button type="button" class="doctors-action-btn secondary" onclick="deactivateUser(<?= $doctor['id_utilisateur'] ?>)" title="Désactiver"><i class="fas fa-ban"></i></button>
                                            <?php endif; ?>
                                            
                                            <button type="button" class="doctors-action-btn secondary" onclick="deleteUser(<?= $doctor['id_utilisateur'] ?>)" title="Supprimer définitivement"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if (!empty($doctors)): ?>
                <div style="padding: 1rem; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 0.875rem;">
                    Affichage de <?= count($doctors) ?> médecin(s) sur <?= $totalDoctors ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modals -->
    <div class="modal fade" id="reasonModal" tabindex="-1" aria-hidden="true" style="display: none;">
        <div class="modal-dialog" style="max-width: 500px; margin: 1.75rem auto;">
            <div class="modal-content" style="background: white; border-radius: 0.5rem; border: 1px solid rgba(0,0,0,.2);">
                <form id="actionForm" method="POST" action="admin-medecins.php">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" id="actionType" name="action">
                    <input type="hidden" id="doctorId" name="id">
                    
                    <div class="modal-header" style="padding: 1rem; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between;">
                        <h5 class="modal-title" id="modalTitle" style="margin: 0; font-size: 1.25rem;">Raison</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeModal('reasonModal')" style="background: none; border: none; font-size: 1.5rem;">&times;</button>
                    </div>
                    <div class="modal-body" style="padding: 1rem;">
                        <div class="mb-3">
                            <label for="reason" class="form-label" style="display: block; margin-bottom: 0.5rem;">Raison (optionnel)</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" style="width: 100%; padding: 0.375rem 0.75rem; border: 1px solid #ced4da; border-radius: 0.25rem;" 
                                      placeholder="Expliquez la raison de cette action..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer" style="padding: 0.75rem; border-top: 1px solid #dee2e6; display: flex; justify-content: flex-end; gap: 0.5rem;">
                        <button type="button" class="dashboard-btn btn-outline" data-bs-dismiss="modal" onclick="closeModal('reasonModal')">Annuler</button>
                        <button type="submit" class="dashboard-btn btn-primary">Confirmer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Verify Diploma -->
    <div class="modal fade" id="verifyDiplomaModal" tabindex="-1" aria-hidden="true" style="display: none;">
        <div class="modal-dialog" style="max-width: 500px; margin: 1.75rem auto;">
             <div class="modal-content" style="background: white; border-radius: 0.5rem; border: 1px solid rgba(0,0,0,.2);">
                <form id="verifyDiplomaForm" method="POST" action="admin-medecins.php">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="verify_diploma">
                    <input type="hidden" id="diplomaDoctorId" name="id">
                    
                    <div class="modal-header" style="padding: 1rem; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between;">
                        <h5 class="modal-title" style="margin: 0; font-size: 1.25rem;">Vérification du diplôme</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="closeModal('verifyDiplomaModal')" style="background: none; border: none; font-size: 1.5rem;">&times;</button>
                    </div>
                    <div class="modal-body" style="padding: 1rem;">
                        <div class="mb-3">
                            <label for="diploma_status" class="form-label" style="display: block; margin-bottom: 0.5rem;">Statut du diplôme</label>
                            <select class="form-select" id="diploma_status" name="diploma_status" required style="width: 100%; padding: 0.375rem 2.25rem 0.375rem 0.75rem; border: 1px solid #ced4da; border-radius: 0.25rem;">
                                <option value="">Sélectionner un statut</option>
                                <option value="validé">Validé</option>
                                <option value="rejeté">Rejeté</option>
                                <option value="en attente">En attente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label" style="display: block; margin-bottom: 0.5rem; margin-top: 1rem;">Commentaire (optionnel)</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3" style="width: 100%; padding: 0.375rem 0.75rem; border: 1px solid #ced4da; border-radius: 0.25rem;"
                                      placeholder="Commentaire sur la vérification..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer" style="padding: 0.75rem; border-top: 1px solid #dee2e6; display: flex; justify-content: flex-end; gap: 0.5rem;">
                        <button type="button" class="dashboard-btn btn-outline" data-bs-dismiss="modal" onclick="closeModal('verifyDiplomaModal')">Annuler</button>
                        <button type="submit" class="dashboard-btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../assets/js/popper.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#doctorsTable').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json' },
                pageLength: 25,
                responsive: true,
                order: [[5, 'desc']]
            });
        });

        // Modal Helpers (Bootstrap 5 JS is included but basic toggling just in case)
        function closeModal(id) {
            document.getElementById(id).classList.remove('show');
            document.getElementById(id).style.display = 'none';
            document.body.classList.remove('modal-open');
             const backdrops = document.getElementsByClassName('modal-backdrop');
             while(backdrops.length > 0){
                 backdrops[0].parentNode.removeChild(backdrops[0]);
             }
        }
        
        // Show functions (Manually triggering style due to potential bootstrap version conflict or just to be safe)
        function showModal(id) {
             const modal = document.getElementById(id);
             modal.style.display = 'block';
             setTimeout(() => modal.classList.add('show'), 10);
             document.body.classList.add('modal-open');
             const backdrop = document.createElement('div');
             backdrop.className = 'modal-backdrop fade show';
             document.body.appendChild(backdrop);
        }

        // Functions
        function approveDoctor(id) {
            if (confirm("Êtes-vous sûr de vouloir approuver ce médecin ?")) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'admin-medecins.php';
                form.innerHTML = `
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function rejectDoctor(id) {
            document.getElementById('actionType').value = 'reject';
            document.getElementById('doctorId').value = id;
            document.getElementById('modalTitle').textContent = 'Raison du rejet';
            showModal('reasonModal');
        }

        function suspendDoctor(id) {
            document.getElementById('actionType').value = 'suspend';
            document.getElementById('doctorId').value = id;
            document.getElementById('modalTitle').textContent = 'Raison de la suspension';
            showModal('reasonModal');
        }

        function showVerifyDiplomaModal(id) {
            document.getElementById('diplomaDoctorId').value = id;
            showModal('verifyDiplomaModal');
        }

        function deactivateUser(id) {
            if (confirm("Désactiver ce compte ?")) window.location.href = `?action=delete&id=${id}`;
        }

        function activateUser(id) {
            if (confirm("Activer ce compte ?")) window.location.href = `?action=activate&id=${id}`;
        }

        function deleteUser(id) {
            if (confirm("Supprimer définitivement ? Irréversible !")) window.location.href = `?action=permanent_delete&id=${id}`;
        }

        function exportToExcel() {
            const btn = document.getElementById('exportBtn');
            const spinner = document.getElementById('exportSpinner');
            btn.disabled = true;
            spinner.classList.remove('d-none');
            setTimeout(() => { window.location.href = '?action=export_excel'; }, 500);
        }

        // Auto-dismiss alerts
        setTimeout(() => {
            document.querySelectorAll('.dashboard-alert-close').forEach(btn => btn.click());
        }, 5000);
        
        document.querySelectorAll('.dashboard-alert-close').forEach(btn => {
            btn.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
        
         document.querySelectorAll('.logout-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>