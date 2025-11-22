<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/auth/sign-in.php');
    exit;
}

require_once __DIR__ . '/../../controllers/AdminController.php';
$adminController = new AdminController();
$usersResult = $adminController->manageUsers('list');
$users = $usersResult['success'] ? $usersResult['users'] : [];

// Gérer les messages de succès/erreur
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Recherche et filtrage
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$statut_filter = $_GET['statut'] ?? '';

if ($search || $role_filter || $statut_filter) {
    $users = array_filter($users, function($user) use ($search, $role_filter, $statut_filter) {
        $match_search = true;
        $match_role = true;
        $match_statut = true;
        
        if ($search) {
            $search_term = strtolower($search);
            $match_search = strpos(strtolower($user['nom']), $search_term) !== false ||
                           strpos(strtolower($user['prenom']), $search_term) !== false ||
                           strpos(strtolower($user['email']), $search_term) !== false;
        }
        
        if ($role_filter) {
            $match_role = $user['role'] === $role_filter;
        }
        
        if ($statut_filter) {
            $match_statut = $user['statut'] === $statut_filter;
        }
        
        return $match_search && $match_role && $match_statut;
    });
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        .table-responsive {
            max-height: 600px;
        }
        .badge {
            font-size: 0.75em;
        }
        .actions-column {
            min-width: 200px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
            <div class="container">
                <a class="navbar-brand" href="admin-dashboard.php">
                    <i class="fas fa-cogs"></i> Administration
                </a>
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="admin-dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link active" href="admin-users.php">
                        <i class="fas fa-users"></i> Utilisateurs
                    </a>
                    <a class="nav-link" href="../frontoffice/auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        </nav>

        <div class="container">
            <!-- En-tête avec titre et bouton d'ajout -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="fas fa-users me-2"></i>Gestion des Utilisateurs
                </h1>
                <a href="admin-create-user.php" class="btn btn-success">
                    <i class="fas fa-user-plus me-1"></i> Nouvel Utilisateur
                </a>
            </div>

            <!-- Messages d'alerte -->
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Filtres et recherche -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i>Filtres et Recherche
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Recherche</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?= htmlspecialchars($search) ?>" placeholder="Nom, prénom ou email...">
                            </div>
                            <div class="col-md-3">
                                <label for="role" class="form-label">Rôle</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="">Tous les rôles</option>
                                    <option value="user" <?= $role_filter === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                                    <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                    <option value="moderator" <?= $role_filter === 'moderator' ? 'selected' : '' ?>>Modérateur</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="statut" class="form-label">Statut</label>
                                <select class="form-select" id="statut" name="statut">
                                    <option value="">Tous les statuts</option>
                                    <option value="actif" <?= $statut_filter === 'actif' ? 'selected' : '' ?>>Actif</option>
                                    <option value="inactif" <?= $statut_filter === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Appliquer
                                </button>
                            </div>
                        </div>
                        <?php if ($search || $role_filter || $statut_filter): ?>
                        <div class="mt-3">
                            <a href="admin-users.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Réinitialiser les filtres
                            </a>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Liste des Utilisateurs
                        <span class="badge bg-primary ms-2"><?= count($users) ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($users)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun utilisateur trouvé</h5>
                            <?php if ($search || $role_filter || $statut_filter): ?>
                                <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                                <a href="admin-users.php" class="btn btn-primary mt-2">
                                    <i class="fas fa-redo me-1"></i> Réinitialiser les filtres
                                </a>
                            <?php else: ?>
                                <p class="text-muted">Commencez par ajouter un nouvel utilisateur</p>
                                <a href="admin-create-user.php" class="btn btn-success mt-2">
                                    <i class="fas fa-user-plus me-1"></i> Ajouter un utilisateur
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom Complet</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                        <th>Statut</th>
                                        <th>Date d'inscription</th>
                                        <th class="actions-column">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): 
                                        $user_id = $user['id_utilisateur'] ?? $user['id'];
                                        $is_current_user = $user_id == $_SESSION['user_id'];
                                    ?>
                                    <tr>
                                        <td><?= $user_id ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $user['role'] === 'admin' ? 'danger' : 
                                                ($user['role'] === 'moderator' ? 'warning' : 'primary') 
                                            ?>">
                                                <?= ucfirst($user['role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $user['statut'] === 'actif' ? 'success' : 'secondary' ?>">
                                                <?= ucfirst($user['statut']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($user['date_inscription'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="admin-edit.php?id=<?= $user_id ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Modifier">
                                                    <i class="fas fa-edit"></i> Modifier
                                                </a>
                                                <?php if (!$is_current_user): ?>
                                                <form method="POST" action="admin-delete.php" 
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.');"
                                                      style="display: inline;">
                                                    <input type="hidden" name="user_id" value="<?= $user_id ?>">
                                                    <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </button>
                                                </form>
                                                <?php else: ?>
                                                <button class="btn btn-outline-secondary" disabled title="Vous ne pouvez pas supprimer votre propre compte">
                                                    <i class="fas fa-trash"></i> Supprimer
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
            </div>

            <div class="mt-4 text-center text-muted">
                <p>© <?= date('Y') ?> Administration - Tous droits réservés</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
       
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>