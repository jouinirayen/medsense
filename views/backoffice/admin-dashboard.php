<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/auth/sign-in.php');
    exit;
}

require_once __DIR__ . '/../../controllers/AdminController.php';
$adminController = new AdminController();
$dashboardData = $adminController->dashboard();

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

$stats = $dashboardData['stats'] ?? [];
$recentUsers = $dashboardData['recentUsers'] ?? [];
$pendingDoctors = $dashboardData['pendingDoctors'] ?? [];

$totalUsers = $stats['total'] ?? 0;
$newThisMonth = $stats['new_this_month'] ?? 0;
$roleStats = $stats['by_role'] ?? [];

function formatDate($dateString)
{
    $date = new DateTime($dateString);
    $now = new DateTime();
    $interval = $now->diff($date);

    if ($interval->days == 0) {
        return "Aujourd'hui à " . $date->format('H:i');
    } elseif ($interval->days == 1) {
        return "Hier à " . $date->format('H:i');
    } elseif ($interval->days < 7) {
        return "Il y a " . $interval->days . " jours";
    } else {
        return $date->format('d/m/Y');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Medsense Medical</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap"
        rel="stylesheet">

    <!-- Shared CSS -->
    <link rel="stylesheet"
        href="../frontoffice/page-accueil/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet"
        href="../backoffice/dashboard_service/css/dashboard.css?v=<?php echo time(); ?>">
    <!-- Vanilla DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>

<body>
    <header class="header">
        <div class="logo-section">
            <img src="../frontoffice/page-accueil/images/logo.jpeg" alt="Logo Medsense"
                style="height: 50px; width: auto;">
        </div>
        <nav class="nav-links">
            <a href="../backoffice/admin_hub.php" class="nav-link active">
                <span class="nav-icon"><i class="fas fa-th-large"></i></span>
                <span>Hub Central</span>
            </a>
            <a href="admin-users.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-users"></i></span>
                <span>Utilisateurs</span>
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
        <section class="hero-section">
            <h1 class="hero-title">Hub Central Administrateur</h1>
            <p class="hero-description">
                Vue d'overview et accès rapide aux fonctionnalités.
            </p>
        </section>

        <!-- Stats Grid -->
        <section class="dashboard-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Utilisateurs</h3>
                        <span class="stat-value"><?= $stats['total'] ?? 0 ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Nouveaux ce mois</h3>
                        <span class="stat-value"><?= $stats['new_this_month'] ?? 0 ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Médecins</h3>
                        <span class="stat-value"><?= $stats['by_role']['medecin'] ?? 0 ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Admins</h3>
                        <span class="stat-value"><?= $stats['by_role']['admin'] ?? 0 ?></span>
                    </div>
                </div>
            </div>

            <div class="row" style="display: flex; gap: 24px; flex-wrap: wrap;">
                <!-- Recent Users -->
                <div class="dashboard-card" style="flex: 1; min-width: 300px;">
                    <div class="dashboard-card-header">
                        <h3 class="dashboard-card-title">
                            <i class="fas fa-user-clock me-2"></i> Inscriptions Récentes
                        </h3>
                    </div>
                    <div class="dashboard-card-body p-0">
                        <?php if (empty($recentUsers)): ?>
                            <div class="dashboard-empty">Aucune inscription récente.</div>
                        <?php else: ?>
                            <div class="dashboard-table-responsive">
                                <table class="dashboard-table">
                                    <thead>
                                        <tr>
                                            <th>Utilisateur</th>
                                            <th>Rôle</th>
                                            <th>Date d'inscription</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentUsers as $user):
                                            $initials = strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1));
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="dashboard-user-info">
                                                        <div class="dashboard-user-avatar">
                                                            <?= $initials ?>
                                                        </div>
                                                        <div>
                                                            <div class="dashboard-user-name">
                                                                <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                                                            </div>
                                                            <div class="dashboard-user-email">
                                                                <?= htmlspecialchars($user['email']) ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="dashboard-badge role-<?= $user['role'] ?>">
                                                        <?= ucfirst($user['role']) ?>
                                                    </span>
                                                </td>
                                                <td><?= formatDate($user['date_inscription']) ?></td>
                                                <td>
                                                    <span class="dashboard-status status-<?= $user['statut'] ?>">
                                                        <?= ucfirst($user['statut']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="dashboard-card-footer">
                        <a href="admin-users.php" class="dashboard-btn btn-outline">
                            <i class="fas fa-list me-1"></i> Voir tous les utilisateurs
                        </a>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h3 class="dashboard-card-title">
                            <i class="fas fa-user-md me-2"></i>Médecins en Attente
                            <?php if (isset($pendingDoctors['count']) && $pendingDoctors['count'] > 0): ?>
                                <span class="dashboard-card-badge danger"><?= $pendingDoctors['count'] ?></span>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="dashboard-card-body">
                        <?php if (empty($pendingDoctors) || (isset($pendingDoctors['success']) && !$pendingDoctors['success']) || (isset($pendingDoctors['count']) && $pendingDoctors['count'] == 0)): ?>
                            <div class="dashboard-empty">
                                <i class="fas fa-user-md fa-3x mb-3"></i>
                                <h5>Aucun médecin en attente</h5>
                                <p>Tous les médecins ont été approuvés</p>
                            </div>
                        <?php else:
                            $doctors = isset($pendingDoctors['doctors']) ? $pendingDoctors['doctors'] : $pendingDoctors;
                            if (!is_array($doctors) || count($doctors) == 0): ?>
                                <div class="dashboard-empty">
                                    <i class="fas fa-user-md fa-3x mb-3"></i>
                                    <h5>Aucun médecin en attente</h5>
                                    <p>Tous les médecins ont été approuvés</p>
                                </div>
                            <?php else: ?>
                                <div class="dashboard-table-responsive">
                                    <table class="dashboard-table">
                                        <thead>
                                            <tr>
                                                <th>Médecin</th>
                                                <th>Spécialité</th>
                                                <th>Date d'inscription</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($doctors as $doctor):
                                                if (is_array($doctor) && isset($doctor['id_utilisateur'])) {
                                                    $doctorId = $doctor['id_utilisateur'];
                                                    $doctorName = htmlspecialchars($doctor['prenom'] . ' ' . $doctor['nom']);
                                                    $doctorEmail = htmlspecialchars($doctor['email']);
                                                    $specialite = isset($doctor['specialite']) ? htmlspecialchars($doctor['specialite']) : 'Non spécifiée';
                                                    $date = formatDate($doctor['date_inscription']);
                                                    $initials = strtoupper(substr($doctor['prenom'], 0, 1) . substr($doctor['nom'], 0, 1));
                                                } else {
                                                    continue;
                                                }
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="dashboard-user-info">
                                                            <div class="dashboard-user-avatar">
                                                                <?= $initials ?>
                                                            </div>
                                                            <div>
                                                                <div class="dashboard-user-name"><?= $doctorName ?></div>
                                                                <div class="dashboard-user-email"><?= $doctorEmail ?></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?= $specialite ?></td>
                                                    <td><?= $date ?></td>
                                                    <td>
                                                        <div class="dashboard-actions">
                                                            <a href="admin-approve-doctor.php?id=<?= $doctorId ?>"
                                                                class="dashboard-btn btn-success btn-sm" title="Approuver">
                                                                <i class="fas fa-check"></i>
                                                            </a>
                                                            <a href="admin-reject-doctor.php?id=<?= $doctorId ?>"
                                                                class="dashboard-btn btn-danger btn-sm" title="Rejeter">
                                                                <i class="fas fa-times"></i>
                                                            </a>
                                                            <a href="admin-view-doctor.php?id=<?= $doctorId ?>"
                                                                class="dashboard-btn btn-info btn-sm" title="Voir détails">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="dashboard-card-footer">
                        <a href="admin-medecins.php?statut=en_attente" class="dashboard-btn btn-outline">
                            <i class="fas fa-list me-1"></i> Gérer tous les médecins en attente
                        </a>
                    </div>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3 class="dashboard-card-title">
                        <i class="fas fa-bolt me-2"></i>Actions Rapides
                    </h3>
                </div>
                <div class="dashboard-card-body">
                    <div class="dashboard-quick-actions">
                        <a href="admin-create-user.php" class="dashboard-quick-action">
                            <div class="dashboard-quick-action-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="dashboard-quick-action-text">Nouvel Utilisateur</div>
                        </a>
                        <a href="admin-medecins.php" class="dashboard-quick-action">
                            <div class="dashboard-quick-action-icon">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div class="dashboard-quick-action-text">Gérer Médecins</div>
                        </a>
                        <a href="admin-users.php" class="dashboard-quick-action">
                            <div class="dashboard-quick-action-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="dashboard-quick-action-text">Gérer Utilisateurs</div>
                        </a>
                        <a href="admin-reports-statistics.php" class="dashboard-quick-action">
                            <div class="dashboard-quick-action-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="dashboard-quick-action-text">Rapports</div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="dashboard-actions">
                <a href="admin-users.php" class="dashboard-btn btn-primary">
                    <i class="fas fa-users me-2"></i> Gestion Utilisateurs
                </a>
                <a href="admin-medecins.php" class="dashboard-btn btn-success">
                    <i class="fas fa-user-md me-2"></i> Gestion Médecins
                </a>

                <a href="../frontoffice/home/index.php" class="dashboard-btn btn-outline">
                    <i class="fas fa-home me-2"></i> Retour au site
                </a>
            </div>
    </main>
    </div>

    <script src="../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../assets/js/popper.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>

    <script>

        document.getElementById('menuToggle').addEventListener('click', function () {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        });

        document.querySelectorAll('.with-submenu').forEach(item => {
            const toggle = item.querySelector('.submenu-toggle');
            const submenu = item.querySelector('.dashboard-submenu');

            if (toggle && submenu) {
                toggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    document.querySelectorAll('.with-submenu').forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.querySelector('.dashboard-submenu').style.display = 'none';
                            otherItem.querySelector('.submenu-toggle').classList.remove('open');
                        }
                    });

                    if (submenu.style.display === 'block') {
                        submenu.style.display = 'none';
                        toggle.classList.remove('open');
                    } else {
                        submenu.style.display = 'block';
                        toggle.classList.add('open');
                    }
                });
            }
        });

        document.querySelectorAll('.dashboard-alert-close').forEach(btn => {
            btn.addEventListener('click', function () {
                this.closest('.dashboard-alert').style.display = 'none';
            });
        });

        setTimeout(() => {
            const alerts = document.querySelectorAll('.dashboard-alert');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);

        document.addEventListener('DOMContentLoaded', function () {
            const cards = document.querySelectorAll('.dashboard-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;

                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 300 + (index * 100));
            });
        });

        document.querySelectorAll('a[href*="logout"]').forEach(link => {
            link.addEventListener('click', function (e) {
                if (!confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>

</html>