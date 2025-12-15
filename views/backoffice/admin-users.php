<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/auth/sign-in.php');
    exit;
}

require_once __DIR__ . '/../../controllers/AdminController.php';
$adminController = new AdminController();
$usersResult = $adminController->manageUsers('list');
$allUsers = $usersResult['success'] ? $usersResult['users'] : [];

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$statut_filter = $_GET['statut'] ?? '';

$users = $allUsers;

if ($search || $role_filter || $statut_filter) {
    $users = array_filter($allUsers, function ($user) use ($search, $role_filter, $statut_filter) {
        $match_search = true;
        $match_role = true;
        $match_statut = true;

        if ($search) {
            $search_term = strtolower(trim($search));
            $nom = strtolower($user['nom'] ?? '');
            $prenom = strtolower($user['prenom'] ?? '');
            $email = strtolower($user['email'] ?? '');

            $match_search = strpos($nom, $search_term) !== false ||
                strpos($prenom, $search_term) !== false ||
                strpos($email, $search_term) !== false;
        }
        if ($role_filter) {
            $match_role = ($user['role'] ?? '') === $role_filter;
        }
        if ($statut_filter) {
            $match_statut = ($user['statut'] ?? '') === $statut_filter;
        }

        return $match_search && $match_role && $match_statut;
    });
    $users = array_values($users);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Medsense Medical</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    <!-- Shared CSS -->
    <link rel="stylesheet"
        href="../frontoffice/page-accueil/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet"
        href="../backoffice/dashboard_service/css/dashboard.css?v=<?php echo time(); ?>">
    <style>
        .users-user-avatar {
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

        .users-user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="logo-section">
            <img src="../images/logo.jpeg" alt="Logo Medsense"
                style="height: 50px; width: auto;">
        </div>
        <nav class="nav-links">
            <a href="../backoffice/admin_hub.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-th-large"></i></span>
                <span>Hub Central</span>
            </a>
            <a href="admin-users.php" class="nav-link active">
                <span class="nav-icon"><i class="fas fa-users"></i></span>
                <span>Utilisateurs</span>
            </a>
            <a href="admin-medecins.php" class="nav-link">
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
        <!-- Hero -->
        <section class="hero-section text-start mb-4">
            <h1 class="hero-title" style="font-size: 1.75rem; text-align: left;">Gestion des Utilisateurs</h1>
            <p class="hero-description" style="text-align: left;">
                Administrez les comptes utilisateurs, leurs rôles et leurs statuts.
            </p>
        </section>

        <!-- Stats Row -->
        <div class="stats-grid mb-4">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Utilisateurs</h3>
                    <span class="stat-value"><?= count($allUsers); ?></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <h3>Utilisateurs Actifs</h3>
                    <span class="stat-value">
                        <?= count(array_filter($allUsers, fn($u) => ($u['statut'] ?? '') === 'actif')); ?>
                    </span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div class="stat-info">
                    <h3>Utilisateurs Inactifs</h3>
                    <span class="stat-value">
                        <?= count(array_filter($allUsers, fn($u) => ($u['statut'] ?? '') === 'inactif')); ?>
                    </span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-info">
                    <h3>Administrateurs</h3>
                    <span class="stat-value">
                        <?= count(array_filter($allUsers, fn($u) => ($u['role'] ?? '') === 'admin')); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Filter & List Card -->
        <div class="dashboard-card animate-fade-in-up">
            <div class="dashboard-card-header" style="justify-content: space-between;">
                <h3 class="dashboard-card-title">
                    <i class="fas fa-filter me-2"></i>Filtres & Actions
                </h3>
                <div class="d-flex gap-2">
                    <a href="admin-export-excel.php?<?= http_build_query(['search' => $search, 'role' => $role_filter, 'statut' => $statut_filter]) ?>"
                        class="dashboard-btn btn-success">
                        <i class="fas fa-file-excel me-1"></i> Exporter
                    </a>
                    <a href="admin-create-user.php" class="dashboard-btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i> Nouveau
                    </a>
                </div>
            </div>
            <div class="dashboard-card-body">
                <form method="GET" action="" id="filterForm"
                    style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 1rem; align-items: end;">
                    <div>
                        <label for="search" class="form-label"
                            style="display: block; margin-bottom: 0.5rem; color: #374151; font-weight: 500;">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search"
                            value="<?= htmlspecialchars($search) ?>" placeholder="Nom, prénom ou email..."
                            style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    </div>
                    <div>
                        <label for="role" class="form-label"
                            style="display: block; margin-bottom: 0.5rem; color: #374151; font-weight: 500;">Rôle</label>
                        <select class="form-select" id="role" name="role"
                            style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                            <option value="">Tous</option>
                            <option value="user" <?= $role_filter === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                            <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="moderator" <?= $role_filter === 'moderator' ? 'selected' : '' ?>>Modérateur
                            </option>
                        </select>
                    </div>
                    <div>
                        <label for="statut" class="form-label"
                            style="display: block; margin-bottom: 0.5rem; color: #374151; font-weight: 500;">Statut</label>
                        <select class="form-select" id="statut" name="statut"
                            style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                            <option value="">Tous</option>
                            <option value="actif" <?= $statut_filter === 'actif' ? 'selected' : '' ?>>Actif</option>
                            <option value="inactif" <?= $statut_filter === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="dashboard-btn btn-primary w-100" style="justify-content: center;">
                            <i class="fas fa-search me-1"></i>
                        </button>
                        <?php if ($search || $role_filter || $statut_filter): ?>
                            <a href="admin-users.php" class="dashboard-btn btn-outline" title="Réinitialiser">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="dashboard-card mt-4">
            <div class="dashboard-card-header">
                <h3 class="dashboard-card-title">
                    <i class="fas fa-list me-2"></i>Liste des Utilisateurs
                    <span class="dashboard-badge role-info ms-2" style="font-size: 0.75rem;"><?= count($users) ?></span>
                </h3>
            </div>
            <div class="dashboard-card-body p-0">
                <?php if (empty($users)): ?>
                    <div class="dashboard-empty" style="text-align: center; padding: 3rem;">
                        <i class="fas fa-users fa-3x mb-3" style="color: #9ca3af;"></i>
                        <h5 style="color: #4b5563;">Aucun utilisateur trouvé</h5>
                        <p style="color: #6b7280;">Modifiez vos filtres ou ajoutez un nouvel utilisateur.</p>
                    </div>
                <?php else: ?>
                    <div class="dashboard-table-responsive">
                        <table class="dashboard-table" id="usersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Utilisateur</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user):
                                    $user_id = $user['id_utilisateur'] ?? $user['id'];
                                    $is_current_user = $user_id == $_SESSION['user_id'];
                                    $initials = strtoupper(substr($user['prenom'] ?? '', 0, 1) . substr($user['nom'] ?? '', 0, 1));
                                    ?>
                                    <tr>
                                        <td><strong>#<?= $user_id ?></strong></td>
                                        <td>
                                            <div class="users-user-info">
                                                <div class="users-user-avatar">
                                                    <?= htmlspecialchars($initials) ?>
                                                </div>
                                                <div class="users-user-name" style="font-weight: 500; color: #111827;">
                                                    <?= htmlspecialchars(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="mailto:<?= htmlspecialchars($user['email']) ?>"
                                                style="color: #4b5563; text-decoration: none;">
                                                <?= htmlspecialchars($user['email']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="dashboard-badge role-<?= $user['role'] ?? 'user' ?>">
                                                <?= ucfirst($user['role'] ?? 'user') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="dashboard-badge role-<?= ($user['statut'] ?? '') === 'actif' ? 'success' : 'secondary' ?>">
                                                <?= ucfirst($user['statut'] ?? 'inactif') ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($user['date_inscription'] ?? 'now')) ?></td>
                                        <td>
                                            <div style="display: flex; gap: 0.5rem;">
                                                <a href="admin-edit.php?id=<?= $user_id ?>"
                                                    class="dashboard-btn btn-primary btn-sm" title="Modifier"
                                                    style="padding: 4px 8px;">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <?php if (!$is_current_user): ?>
                                                    <?php if (($user['statut'] ?? '') === 'actif'): ?>
                                                        <form method="POST" action="admin-deactivate.php"
                                                            onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver cet utilisateur ?');"
                                                            style="display: inline;">
                                                            <input type="hidden" name="user_id" value="<?= $user_id ?>">
                                                            <button type="submit" class="dashboard-btn btn-outline btn-sm"
                                                                style="color: #d97706; border-color: #d97706; padding: 4px 8px;"
                                                                title="Désactiver">
                                                                <i class="fas fa-user-slash"></i>
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <form method="POST" action="admin-activate.php"
                                                            onsubmit="return confirm('Êtes-vous sûr de vouloir activer cet utilisateur ?');"
                                                            style="display: inline;">
                                                            <input type="hidden" name="user_id" value="<?= $user_id ?>">
                                                            <button type="submit" class="dashboard-btn btn-success btn-sm"
                                                                style="padding: 4px 8px;" title="Activer">
                                                                <i class="fas fa-user-check"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <button class="dashboard-btn btn-outline btn-sm" disabled
                                                        style="padding: 4px 8px; opacity: 0.5; cursor: not-allowed;"
                                                        title="Vous ne pouvez pas modifier votre propre statut">
                                                        <i class="fas fa-user-lock"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($users)): ?>
                <div style="padding: 1rem; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 0.875rem;">
                    Affichage de <?= count($users) ?> utilisateur(s) sur <?= count($allUsers) ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../assets/js/popper.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#usersTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
                },
                pageLength: 25,
                responsive: true,
                order: [[5, 'desc']],
                columnDefs: [
                    {
                        targets: [6],
                        orderable: false,
                        searchable: false
                    }
                ]
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