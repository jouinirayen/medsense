<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/auth/sign-in.php');
    exit;
}

require_once __DIR__ . '/../../controllers/AdminController.php';
$adminController = new AdminController();
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$reportType = $_GET['report_type'] ?? 'overview';
$dashboardData = $adminController->dashboard();
$stats = $dashboardData['stats'] ?? [];
$recentUsers = $dashboardData['recentUsers'] ?? [];
$pendingDoctors = $dashboardData['pendingDoctors'] ?? [];
$usersResult = $adminController->getAllUsers();
$allUsers = $usersResult['success'] ? $usersResult['users'] : [];
$totalUsers = $stats['total'] ?? 0;
$newThisMonth = $stats['new_this_month'] ?? 0;
$roleStats = $stats['by_role'] ?? [];
$totalDoctors = 0;
$activeUsers = 0;
$pendingDoctorsCount = 0;
$rolesSummary = [
    'admin' => ['total' => 0, 'actif' => 0, 'inactif' => 0, 'en_attente' => 0],
    'medecin' => ['total' => 0, 'actif' => 0, 'inactif' => 0, 'en_attente' => 0],
    'user' => ['total' => 0, 'actif' => 0, 'inactif' => 0, 'en_attente' => 0],
    'patient' => ['total' => 0, 'actif' => 0, 'inactif' => 0, 'en_attente' => 0]
];
$allowedStatuses = ['actif', 'inactif', 'en_attente', 'rejeté', 'suspendu'];
$statusStats = array_fill_keys($allowedStatuses, 0);

foreach ($allUsers as $user) {
    $role = $user['role'] ?? 'user';
    $status = $user['statut'] ?? 'inactif';
    if (!isset($rolesSummary[$role])) {
        $rolesSummary[$role] = ['total' => 0, 'actif' => 0, 'inactif' => 0, 'en_attente' => 0];
    }
    $rolesSummary[$role]['total']++;
    if (in_array($status, $allowedStatuses)) {
        $rolesSummary[$role][$status] = ($rolesSummary[$role][$status] ?? 0) + 1;
        $statusStats[$status] = ($statusStats[$status] ?? 0) + 1;
    }
    if ($role === 'medecin') {
        $totalDoctors++;
        if ($status === 'en_attente') {
            $pendingDoctorsCount++;
        }
    }
    if ($status === 'actif') {
        $activeUsers++;
    }
}

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

function formatNumber($number)
{
    return number_format($number, 0, ',', ' ');
}

function getRoleColor($role)
{
    $colors = [
        'admin' => '#dc3545',
        'medecin' => '#17a2b8',
        'user' => '#28a745',
        'patient' => '#ffc107',
        'moderator' => '#6c757d'
    ];
    return $colors[$role] ?? '#007bff';
}

function getStatusColor($status)
{
    $colors = [
        'actif' => '#28a745',
        'inactif' => '#6c757d',
        'en_attente' => '#ffc107',
        'rejeté' => '#dc3545',
        'suspendu' => '#343a40'
    ];
    return $colors[$status] ?? '#007bff';
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapports Statistiques - Medsense Medical</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Shared CSS -->
    <link rel="stylesheet"
        href="../frontoffice/page-accueil/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet"
        href="../backoffice/dashboard_service/css/dashboard.css?v=<?php echo time(); ?>">

    <style>
        /* Specific page overrides if strictly needed, minimized */
        .dashboard-chart-container {
            height: 300px;
            position: relative;
        }

        .dashboard-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
            justify-content: center;
        }

        .dashboard-legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dashboard-legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="logo-section">
            <img src="../frontoffice/page-accueil/images/logo.jpeg" alt="Logo Medsense"
                style="height: 50px; width: auto;">
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
            <a href="admin-medecins.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-user-md"></i></span>
                <span>Médecins</span>
            </a>
            <a href="admin-reports-statistics.php" class="nav-link active">
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
        <!-- Hero -->
        <section class="hero-section mb-4">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 class="hero-title" style="font-size: 1.75rem; text-align: left;">Rapports Statistiques</h1>
                    <p class="hero-description" style="text-align: left;">
                        Analyse détaillée des données du système.
                    </p>
                </div>
                <a href="admin-dashboard.php" class="dashboard-btn btn-outline" style="background: white;">
                    <i class="fas fa-arrow-left me-2"></i> Retour
                </a>
            </div>
        </section>

        <?php if ($success_message): ?>
            <div
                style="padding: 1rem; background: #dcfce7; color: #166534; border-radius: 12px; margin-bottom: 2rem; border: 1px solid #bbf7d0;">
                <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div
                style="padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 12px; margin-bottom: 2rem; border: 1px solid #fecaca;">
                <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <!-- Filter Card -->
        <div class="dashboard-card mb-4" style="border-left: 4px solid #3b82f6;">
            <div class="dashboard-card-header">
                <h3 class="dashboard-card-title">
                    <i class="fas fa-filter me-2"></i>Filtres de Rapport
                </h3>
            </div>
            <div class="dashboard-card-body">
                <form method="GET" action=""
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end;">
                    <div class="dashboard-form-group">
                        <label class="dashboard-form-label"
                            style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Date de
                            début</label>
                        <input type="date" class="dashboard-form-control" name="start_date"
                            value="<?= htmlspecialchars($startDate) ?>" max="<?= date('Y-m-d') ?>"
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px;">
                    </div>

                    <div class="dashboard-form-group">
                        <label class="dashboard-form-label"
                            style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Date de
                            fin</label>
                        <input type="date" class="dashboard-form-control" name="end_date"
                            value="<?= htmlspecialchars($endDate) ?>" max="<?= date('Y-m-d') ?>"
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px;">
                    </div>

                    <div class="dashboard-form-group">
                        <label class="dashboard-form-label"
                            style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Type de
                            rapport</label>
                        <select class="dashboard-form-control" name="report_type"
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px;">
                            <option value="overview" <?= $reportType === 'overview' ? 'selected' : '' ?>>Vue d'ensemble
                            </option>
                            <option value="users" <?= $reportType === 'users' ? 'selected' : '' ?>>Utilisateurs</option>
                            <option value="doctors" <?= $reportType === 'doctors' ? 'selected' : '' ?>>Médecins</option>
                        </select>
                    </div>

                    <div class="dashboard-form-group">
                        <button type="submit" class="dashboard-btn btn-primary" style="width: 100%;">
                            <i class="fas fa-chart-bar me-1"></i> Générer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid mb-4">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Utilisateurs</h3>
                    <span class="stat-value"><?= formatNumber($totalUsers) ?></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-info">
                    <h3>Nouveaux ce mois</h3>
                    <span class="stat-value"><?= formatNumber($newThisMonth) ?></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="stat-info">
                    <h3>Médecins</h3>
                    <span class="stat-value"><?= formatNumber($totalDoctors) ?></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>En attente</h3>
                    <span class="stat-value"><?= formatNumber($pendingDoctorsCount) ?></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <h3>Actifs</h3>
                    <span class="stat-value"><?= formatNumber($activeUsers) ?></span>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 24px; margin-bottom: 30px;">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3 class="dashboard-card-title">
                        <i class="fas fa-chart-pie me-2"></i>Répartition par Rôle
                    </h3>
                </div>
                <div class="dashboard-card-body">
                    <div class="dashboard-chart-container">
                        <canvas id="roleChart"></canvas>
                    </div>
                    <div class="dashboard-legend" id="roleLegend"></div>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3 class="dashboard-card-title">
                        <i class="fas fa-chart-bar me-2"></i>Répartition par Statut
                    </h3>
                </div>
                <div class="dashboard-card-body">
                    <div class="dashboard-chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="dashboard-legend" id="statusLegend"></div>
                </div>
            </div>
        </div>

        <!-- Detail Table -->
        <div class="dashboard-card mb-4">
            <div class="dashboard-card-header">
                <h3 class="dashboard-card-title">
                    <i class="fas fa-table me-2"></i>Statistiques Détaillées
                </h3>
            </div>
            <div class="dashboard-card-body p-0">
                <div class="dashboard-table-responsive">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Rôle</th>
                                <th>Total</th>
                                <th>Actifs</th>
                                <th>Inactifs</th>
                                <th>En attente</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($rolesSummary as $role => $stats):
                                if ($stats['total'] > 0):
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="dashboard-badge role-<?= $role ?>">
                                                <?= ucfirst($role) ?>
                                            </span>
                                        </td>
                                        <td><strong><?= formatNumber($stats['total']) ?></strong></td>
                                        <td>
                                            <span class="dashboard-badge status-actif">
                                                <?= formatNumber($stats['actif'] ?? 0) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="dashboard-badge status-inactif">
                                                <?= formatNumber($stats['inactif'] ?? 0) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="dashboard-badge status-en_attente">
                                                <?= formatNumber($stats['en_attente'] ?? 0) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php
                                endif;
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Export Actions -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h3 class="dashboard-card-title">
                    <i class="fas fa-file-export me-2"></i>Export des Données
                </h3>
            </div>
            <div class="dashboard-card-body">
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="admin-export-excel.php?type=statistics&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>"
                        class="dashboard-btn btn-success">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </a>
                    <a href="admin-export-pdf.php?type=statistics&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>"
                        class="dashboard-btn btn-danger">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </a>
                    <a href="javascript:void(0);" onclick="window.print()" class="dashboard-btn btn-primary">
                        <i class="fas fa-print me-1"></i> Imprimer
                    </a>
                    <a href="admin-reports-statistics.php" class="dashboard-btn btn-outline" style="background: white;">
                        <i class="fas fa-redo me-1"></i> Actualiser
                    </a>
                </div>
            </div>
        </div>

    </main>

    <script src="../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../assets/js/popper.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>

    <script>
        const roleData = {
            labels: [
                <?php
                $roleLabels = [];
                $roleCounts = [];
                $roleColors = [];

                foreach ($rolesSummary as $role => $stats):
                    if ($stats['total'] > 0):
                        $roleLabels[] = ucfirst($role);
                        $roleCounts[] = $stats['total'];
                        $roleColors[] = getRoleColor($role);
                    endif;
                endforeach;

                echo '"' . implode('","', $roleLabels) . '"';
                ?>
            ],
            datasets: [{
                data: [<?php echo implode(',', $roleCounts); ?>],
                backgroundColor: [<?php echo '"' . implode('","', $roleColors) . '"'; ?>],
                borderWidth: 1
            }]
        };

        const statusData = {
            labels: [
                <?php
                $statusLabels = [];
                $statusCounts = [];
                $statusColors = [];

                foreach ($statusStats as $status => $count):
                    if ($count > 0):
                        $statusLabels[] = ucfirst($status);
                        $statusCounts[] = $count;
                        $statusColors[] = getStatusColor($status);
                    endif;
                endforeach;

                echo '"' . implode('","', $statusLabels) . '"';
                ?>
            ],
            datasets: [{
                data: [<?php echo implode(',', $statusCounts); ?>],
                backgroundColor: [<?php echo '"' . implode('","', $statusColors) . '"'; ?>],
                borderWidth: 1
            }]
        };

        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                            label += context.formattedValue + ' (' + percentage + '%)';
                            return label;
                        }
                    }
                }
            }
        };

        document.addEventListener('DOMContentLoaded', function () {
            const roleCtx = document.getElementById('roleChart').getContext('2d');
            new Chart(roleCtx, {
                type: 'pie',
                data: roleData,
                options: chartOptions
            });

            const roleLegend = document.getElementById('roleLegend');
            roleData.labels.forEach((label, index) => {
                const legendItem = document.createElement('div');
                legendItem.className = 'dashboard-legend-item';
                legendItem.innerHTML = `
                    <div class="dashboard-legend-color" style="background-color: ${roleData.datasets[0].backgroundColor[index]}"></div>
                    <span>${label}</span>
                `;
                roleLegend.appendChild(legendItem);
            });

            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: statusData,
                options: chartOptions
            });

            const statusLegend = document.getElementById('statusLegend');
            statusData.labels.forEach((label, index) => {
                const legendItem = document.createElement('div');
                legendItem.className = 'dashboard-legend-item';
                legendItem.innerHTML = `
                    <div class="dashboard-legend-color" style="background-color: ${statusData.datasets[0].backgroundColor[index]}"></div>
                    <span>${label}</span>
                `;
                statusLegend.appendChild(legendItem);
            });
        });

        document.querySelectorAll('.logout-link').forEach(link => {
            link.addEventListener('click', function (e) {
                if (!confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>

</html>