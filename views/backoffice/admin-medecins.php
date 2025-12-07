<?php
// Démarrage de session sécurisé
session_start([
    'cookie_secure' => false,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);

// Vérification des permissions admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../frontoffice/auth/sign-in.php');
    exit;
}

require_once __DIR__ . '/../../controllers/AdminController.php';

$adminController = new AdminController();

// CORRECTION PRINCIPALE : Lire l'action et l'ID selon la méthode HTTP
$action = '';
$doctorId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pour les requêtes POST, lire depuis INPUT_POST
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING) ?? '';
    $doctorId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Pour les requêtes GET, lire depuis INPUT_GET
    $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?? '';
    $doctorId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
}

// DEBUG: Vérifiez ce qui est reçu
error_log("Méthode HTTP: " . $_SERVER['REQUEST_METHOD']);
error_log("Action reçue: " . $action);
error_log("ID reçu: " . $doctorId);

// Gérer les requêtes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Validation CSRF
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

// Fonction pour gérer les messages de résultat (utilisée dans les actions GET)
function handleResultMessage($result) {
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
}

// Gérer les actions GET avec validation
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

// Récupérer les médecins avec filtres sécurisés
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

// Récupérer les statistiques
$statsResult = $adminController->getApprovalStats();
$statusStats = $statsResult['status_stats'] ?? [];
$weeklyApproved = $statsResult['weekly_approved'] ?? 0;

// Récupérer les médecins en attente
$pendingResult = $adminController->getPendingDoctors();
$pendingDoctors = $pendingResult['doctors'] ?? [];
$pendingCount = $pendingResult['count'] ?? 0;

// Calculer les statistiques par statut
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

// Générer un token CSRF pour les formulaires
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Médecins - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        /* Styles existants améliorés */
        .card-stat {
            transition: transform 0.3s;
            height: 120px;
        }
        .card-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .pending-badge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        .doctor-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .btn-action {
            margin: 1px;
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .action-buttons {
            min-width: 200px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .diplome-actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .export-btn {
            position: relative;
        }
        .export-btn .spinner-border {
            position: absolute;
            top: 50%;
            left: 50%;
            margin-top: -10px;
            margin-left: -10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center py-3 mb-4 border-bottom">
            <h1 class="h2"><i class="bi bi-heart-pulse me-2"></i> Gestion des Médecins</h1>
            <div>
                <a href="admin-dashboard.php" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
                <button type="button" class="btn btn-success export-btn" onclick="exportToExcel()" id="exportBtn">
                    <i class="bi bi-file-excel"></i> Exporter Excel
                    <span class="spinner-border spinner-border-sm d-none" role="status" id="exportSpinner"></span>
                </button>
            </div>
        </div>

        <!-- Messages d'alerte -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-2 col-6 mb-3">
                <div class="card card-stat border-primary h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <h5 class="card-title text-muted">Total</h5>
                        <h2 class="text-primary"><?= htmlspecialchars($totalDoctors) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="card card-stat border-success h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <h5 class="card-title text-muted">Actifs</h5>
                        <h2 class="text-success"><?= htmlspecialchars($statutCounts['actif']) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="card card-stat border-warning pending-badge h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <h5 class="card-title text-muted">En Attente</h5>
                        <h2 class="text-warning"><?= htmlspecialchars($pendingCount) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="card card-stat border-danger h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <h5 class="card-title text-muted">Rejetés</h5>
                        <h2 class="text-danger"><?= htmlspecialchars($statutCounts['rejeté']) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="card card-stat border-secondary h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <h5 class="card-title text-muted">Suspendus</h5>
                        <h2 class="text-secondary"><?= htmlspecialchars($statutCounts['suspendu']) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="card card-stat border-info h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <h5 class="card-title text-muted">Cette semaine</h5>
                        <h2 class="text-info"><?= htmlspecialchars($weeklyApproved) ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3" id="filterForm">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Rechercher (nom, prénom, email, spécialité)" 
                               value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="statut" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="actif" <?= ($statut ?? '') == 'actif' ? 'selected' : '' ?>>Actif</option>
                            <option value="inactif" <?= ($statut ?? '') == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                            <option value="en_attente" <?= ($statut ?? '') == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                            <option value="rejeté" <?= ($statut ?? '') == 'rejeté' ? 'selected' : '' ?>>Rejeté</option>
                            <option value="suspendu" <?= ($statut ?? '') == 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des médecins -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Liste des médecins (<?= htmlspecialchars($totalDoctors) ?>)</h5>
                <span>
                    <?php if (!empty($search) || !empty($statut)): ?>
                        <a href="admin-medecins.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Réinitialiser
                        </a>
                    <?php endif; ?>
                </span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($doctors)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-person-x" style="font-size: 3rem; color: #6c757d;"></i>
                        <h5 class="mt-3">Aucun médecin trouvé</h5>
                        <?php if (!empty($search) || !empty($statut)): ?>
                            <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                        <?php else: ?>
                            <p class="text-muted">Aucun médecin n'est inscrit pour le moment</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="doctorsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Email</th>
                                    <th>Spécialité</th>
                                    <th>Date Inscription</th>
                                    <th>Diplôme</th>
                                    <th>Statut</th>
                                    <th class="action-buttons">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($doctors as $doctor): ?>
                                    <?php
                                    $initials = strtoupper(
                                        substr($doctor['prenom'] ?? '', 0, 1) . 
                                        substr($doctor['nom'] ?? '', 0, 1)
                                    );
                                    $statusClass = match($doctor['statut'] ?? '') {
                                        'actif' => 'bg-success',
                                        'inactif' => 'bg-secondary',
                                        'en_attente' => 'bg-warning',
                                        'rejeté' => 'bg-danger',
                                        'suspendu' => 'bg-dark',
                                        default => 'bg-secondary'
                                    };
                                    $diplomeStatutClass = match($doctor['diplome_statut'] ?? '') {
                                        'validé' => 'bg-success',
                                        'en attente' => 'bg-warning',
                                        'rejeté' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($doctor['id_utilisateur']) ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="doctor-avatar me-2">
                                                    <?= htmlspecialchars($initials) ?>
                                                </div>
                                                <?= htmlspecialchars($doctor['nom']) ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($doctor['prenom']) ?></td>
                                        <td>
                                            <a href="mailto:<?= htmlspecialchars($doctor['email']) ?>">
                                                <?= htmlspecialchars($doctor['email']) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($doctor['specialite'] ?? 'Non spécifiée') ?></td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($doctor['date_inscription'])) ?>
                                        </td>
                                        <td>
                                          <div class="diplome-actions">
        <?php if (!empty($doctor['diplome_path'])): ?>
            <?php
            // Construction du chemin correct pour le diplôme
            $diplome_path = $doctor['diplome_path'];
            $full_diplome_path = __DIR__ . '/../../' . $diplome_path;
            ?>
            <a href="../<?= htmlspecialchars($diplome_path) ?>" 
               target="_blank" 
               class="badge bg-info text-decoration-none" 
               title="Voir le diplôme"
               onclick="return checkDiplomaExists(<?= $doctor['id_utilisateur'] ?>, '<?= htmlspecialchars($diplome_path) ?>')">
                <i class="bi bi-eye"></i>
            </a>
            <span class="badge <?= $diplomeStatutClass ?>">
                <?= $doctor['diplome_statut'] ?? 'Non vérifié' ?>
            </span>
            <button type="button" 
                    class="btn btn-sm btn-outline-primary"
                    onclick="showVerifyDiplomaModal(<?= $doctor['id_utilisateur'] ?>)"
                    title="Vérifier le diplôme">
                <i class="bi bi-check-circle"></i>
            </button>
        <?php else: ?>
            <span class="badge bg-secondary">Non fourni</span>
        <?php endif; ?>
    </div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $statusClass ?> status-badge">
                                                <?= $doctor['statut'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <!-- Actions selon le statut -->
                                                <?php if ($doctor['statut'] === 'en_attente'): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-success btn-action" 
                                                            onclick="approveDoctor(<?= $doctor['id_utilisateur'] ?>)" 
                                                            title="Approuver">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger btn-action" 
                                                            onclick="rejectDoctor(<?= $doctor['id_utilisateur'] ?>)" 
                                                            title="Rejeter">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($doctor['statut'] === 'actif'): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-warning btn-action" 
                                                            onclick="suspendDoctor(<?= $doctor['id_utilisateur'] ?>)" 
                                                            title="Suspendre">
                                                        <i class="bi bi-pause"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if (in_array($doctor['statut'], ['inactif', 'suspendu'])): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-success btn-action" 
                                                            onclick="activateUser(<?= $doctor['id_utilisateur'] ?>)" 
                                                            title="Activer/Réactiver">
                                                        <i class="bi bi-power"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($doctor['statut'] !== 'inactif'): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-secondary btn-action" 
                                                            onclick="deactivateUser(<?= $doctor['id_utilisateur'] ?>)" 
                                                            title="Désactiver">
                                                        <i class="bi bi-slash-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger btn-action" 
                                                        onclick="deleteUser(<?= $doctor['id_utilisateur'] ?>)" 
                                                        title="Supprimer définitivement">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($doctors)): ?>
                <div class="card-footer text-muted d-flex justify-content-between align-items-center">
                    <span>Affichage de <?= count($doctors) ?> médecin(s) sur <?= $totalDoctors ?></span>
                    <small>Dernière mise à jour : <?= date('H:i:s') ?></small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal pour la raison du rejet/suspension -->
    <div class="modal fade" id="reasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="actionForm" method="POST" action="admin-medecins.php">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" id="actionType" name="action">
                    <input type="hidden" id="doctorId" name="id">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Raison</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="reason" class="form-label">Raison (optionnel)</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" 
                                      placeholder="Expliquez la raison de cette action..."></textarea>
                            <div class="form-text">Cette raison sera envoyée au médecin par email.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Confirmer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal pour vérification de diplôme -->
    <div class="modal fade" id="verifyDiplomaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="verifyDiplomaForm" method="POST" action="admin-medecins.php">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="verify_diploma">
                    <input type="hidden" id="diplomaDoctorId" name="id">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Vérification du diplôme</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="diploma_status" class="form-label">Statut du diplôme</label>
                            <select class="form-select" id="diploma_status" name="diploma_status" required>
                                <option value="">Sélectionner un statut</option>
                                <option value="validé">Validé</option>
                                <option value="rejeté">Rejeté</option>
                                <option value="en attente">En attente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Commentaire (optionnel)</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3" 
                                      placeholder="Commentaire sur la vérification..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Inclure jQuery pour DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Initialisation de DataTables
        $(document).ready(function() {
            $('#doctorsTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
                },
                pageLength: 25,
                responsive: true,
                order: [[5, 'desc']], // Tri par date d'inscription décroissante
                columnDefs: [
                    {
                        targets: [8], // Colonne actions
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });

        // Fonctions pour les actions
        function approveDoctor(id) {
            console.log("Tentative d'approbation pour l'ID:", id);
            console.log("Token CSRF:", '<?= $_SESSION['csrf_token'] ?>');
            
            if (confirm("Êtes-vous sûr de vouloir approuver ce médecin ?")) {
                // Créer un formulaire temporaire
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'admin-medecins.php';
                form.style.display = 'none';
                
                // Ajouter les champs
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = 'csrf_token';
                csrf.value = '<?= $_SESSION['csrf_token'] ?>';
                form.appendChild(csrf);
                
                const action = document.createElement('input');
                action.type = 'hidden';
                action.name = 'action';
                action.value = 'approve';
                form.appendChild(action);
                
                const doctorId = document.createElement('input');
                doctorId.type = 'hidden';
                doctorId.name = 'id';
                doctorId.value = id;
                form.appendChild(doctorId);
                
                // Ajouter au body et soumettre
                document.body.appendChild(form);
                console.log("Soumission du formulaire pour approuver le médecin ID:", id);
                form.submit();
            }
        }

        function rejectDoctor(id) {
            document.getElementById('actionForm').action = 'admin-medecins.php';
            document.getElementById('actionType').value = 'reject';
            document.getElementById('doctorId').value = id;
            document.getElementById('modalTitle').textContent = 'Raison du rejet';
            new bootstrap.Modal(document.getElementById('reasonModal')).show();
        }

        function suspendDoctor(id) {
            document.getElementById('actionForm').action = 'admin-medecins.php';
            document.getElementById('actionType').value = 'suspend';
            document.getElementById('doctorId').value = id;
            document.getElementById('modalTitle').textContent = 'Raison de la suspension';
            new bootstrap.Modal(document.getElementById('reasonModal')).show();
        }

        function showVerifyDiplomaModal(id) {
            document.getElementById('diplomaDoctorId').value = id;
            new bootstrap.Modal(document.getElementById('verifyDiplomaModal')).show();
        }

        function deactivateUser(id) {
            if (confirm("Êtes-vous sûr de vouloir désactiver ce compte ?")) {
                window.location.href = `?action=delete&id=${id}`;
            }
        }

        function activateUser(id) {
            if (confirm("Êtes-vous sûr de vouloir activer/réactiver ce compte ?")) {
                window.location.href = `?action=activate&id=${id}`;
            }
        }

        function deleteUser(id) {
            if (confirm("Êtes-vous sûr de vouloir supprimer définitivement ce médecin ?\n\nCette action est irréversible !")) {
                window.location.href = `?action=permanent_delete&id=${id}`;
            }
        }

        function exportToExcel() {
            const btn = document.getElementById('exportBtn');
            const spinner = document.getElementById('exportSpinner');
            
            btn.disabled = true;
            spinner.classList.remove('d-none');
            
            setTimeout(() => {
                window.location.href = '?action=export_excel';
            }, 500);
        }

        // Auto-dismiss des alertes après 5 secondes
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Gestion de la soumission du formulaire de diplôme
        document.getElementById('verifyDiplomaForm').addEventListener('submit', function(e) {
            const status = document.getElementById('diploma_status').value;
            if (!status) {
                e.preventDefault();
                alert('Veuillez sélectionner un statut pour le diplôme.');
            }
        });

        // Fonction pour vérifier si le diplôme existe
        function checkDiplomaExists(doctorId, diplomaPath) {
            console.log("Vérification du diplôme pour le médecin ID:", doctorId);
            console.log("Chemin du diplôme:", diplomaPath);
            return true; // Autoriser l'ouverture
        }
    </script>
</body>
</html>