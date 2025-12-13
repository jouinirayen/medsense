<?php
include_once '../../controllers/AdminController.php';

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/auth/sign-in.php');
    exit;
}

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header('Location: admin-users.php');
    exit;
}

$adminController = new AdminController();
$userResult = $adminController->manageUsers('get', null, $user_id);

if (!$userResult['success']) {
    header('Location: admin-users.php');
    exit;
}

$user = $userResult['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $adminController->manageUsers('update', $_POST, $user_id);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        header('Location: admin-users.php');
        exit;
    } else {
        $error_message = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Utilisateur - Medcare Medical</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/css/flaticon.css">
    <link rel="stylesheet" href="../assets/vendors/fontawesome/css/all.min.css">
    
    <!-- main css -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    
    <style>
        :root {
            --primary-color: #2c7be5;
            --secondary-color: #6c757d;
            --success-color: #00d97e;
            --info-color: #39afd1;
            --warning-color: #f6c343;
            --danger-color: #e63757;
            --light-color: #f9fafd;
            --dark-color: #12263f;
            --sidebar-width: 260px;
            --medical-color: #17a2b8;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
            color: #333;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color) 0%, #1a5bb8 100%);
            color: white;
            padding: 0;
            z-index: 1000;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .sidebar-menu .nav-link:hover, 
        .sidebar-menu .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid white;
        }
        
        .sidebar-menu .nav-link i {
            width: 24px;
            margin-right: 10px;
        }
        
        .sidebar-submenu {
            padding-left: 40px;
        }
        
        .sidebar-submenu .nav-link {
            padding: 8px 20px;
            font-size: 0.9rem;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
        }
        
        .top-bar {
            background-color: white;
            border-radius: 10px;
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #e3ebf6;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 0.75rem;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 123, 229, 0.25);
        }
        
        .btn-view-all {
            color: var(--primary-color);
            font-weight: 500;
            text-decoration: none;
        }
        
        .btn-view-all:hover {
            text-decoration: underline;
        }
        
        .btn-dashboard {
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            text-align: center;
        }
        
        .btn-primary {
            background: #3f51b5;
            color: white;
        }
        
        .btn-primary:hover {
            background: #303f9f;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(63, 81, 181, 0.3);
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
            transform: translateY(-2px);
        }
        
        .user-info-card {
            border-left: 4px solid var(--primary-color);
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto 1rem;
        }
        
        .role-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .role-badge.user {
            background: rgba(56, 161, 105, 0.1);
            color: var(--success-color);
        }
        
        .role-badge.admin {
            background: rgba(230, 55, 87, 0.1);
            color: var(--danger-color);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .status-badge.actif {
            background: rgba(0, 217, 126, 0.1);
            color: var(--success-color);
        }
        
        .status-badge.inactif {
            background: rgba(108, 117, 125, 0.1);
            color: var(--secondary-color);
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar .nav-link span {
                display: none;
            }
            
            .sidebar .sidebar-header h3 {
                display: none;
            }
            
            .sidebar .nav-link i {
                margin-right: 0;
            }
            
            .main-content {
                margin-left: 70px;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-hospital me-2"></i> MédiCare</h3>
        </div>
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="admin-dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tableau de Bord</span>
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <small class="text-uppercase text-muted ms-3">Gestion Médicale</small>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#appointmentsMenu">
                        <i class="fas fa-calendar-check"></i>
                        <span>Rendez-vous</span>
                        <i class="fas fa-chevron-down float-end mt-1"></i>
                    </a>
                    <div class="collapse" id="appointmentsMenu">
                        <ul class="nav flex-column sidebar-submenu">
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span>Tous les rendez-vous</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span>Nouveau rendez-vous</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span>Calendrier</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-user-injured"></i>
                        <span>Patients</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-user-md"></i>
                        <span>Médecins</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="admin-users.php">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-prescription"></i>
                        <span>Ordonnances</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Facturation</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-cog"></i>
                        <span>Paramètres</span>
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <small class="text-uppercase text-muted ms-3">Rapports</small>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-chart-bar"></i>
                        <span>Rapports statistiques</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Audit médical</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Modifier Utilisateur</h4>
            <div class="d-flex align-items-center">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <span class="ms-2">Dr. Admin</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Mon profil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="../../../controllers/logout.php" 
                               onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                                <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="fas fa-user-edit me-2"></i>Modifier l'Utilisateur</h4>
                            <a href="admin-users.php" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Retour à la liste
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (isset($error_message)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error_message) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <!-- Informations de l'utilisateur -->
                            <div class="card user-info-card mb-4">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-3 text-center">
                                            <div class="user-avatar">
                                                <?= strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)) ?>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <h5><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h5>
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($user['email']) ?>
                                            </p>
                                            <div class="d-flex gap-3">
                                                <div>
                                                    <span class="text-muted">Rôle actuel:</span>
                                                    <span class="role-badge <?= $user['role'] ?> ms-2">
                                                        <?= ucfirst($user['role']) ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="text-muted">Statut actuel:</span>
                                                    <span class="status-badge <?= $user['statut'] ?> ms-2">
                                                        <?= ucfirst($user['statut']) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" id="editUserForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="role" class="form-label">Rôle <span class="text-danger">*</span></label>
                                            <select class="form-select" id="role" name="role" required>
                                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                            </select>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle text-info me-1"></i>
                                                Définir les permissions d'accès de l'utilisateur
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="statut" class="form-label">Statut du compte <span class="text-danger">*</span></label>
                                            <select class="form-select" id="statut" name="statut" required>
                                                <option value="actif" <?= $user['statut'] === 'actif' ? 'selected' : '' ?>>Actif - Compte accessible</option>
                                                <option value="inactif" <?= $user['statut'] === 'inactif' ? 'selected' : '' ?>>Inactif - Compte désactivé</option>
                                            </select>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle text-info me-1"></i>
                                                Activer ou désactiver l'accès au compte
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-warning mt-4">
                                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Attention</h6>
                                    <small>
                                        La modification du rôle et du statut prend effet immédiatement. 
                                        Un compte désactivé ne pourra plus se connecter au système.
                                    </small>
                                </div>

                                <div class="mt-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Enregistrer les modifications
                                    </button>
                                    <a href="admin-users.php" class="btn btn-secondary">Annuler</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Actions -->
            <div class="dashboard-actions mt-4">
                <a href="admin-dashboard.php" class="btn-dashboard btn-secondary">
                    <i class="fas fa-tachometer-alt"></i> Retour au Dashboard
                </a>
                <a href="admin-users.php" class="btn-dashboard btn-secondary">
                    <i class="fas fa-users"></i> Liste des Utilisateurs
                </a>
                <a href="../frontoffice/home/index.php" class="btn-dashboard btn-secondary">
                    <i class="fas fa-home"></i> Retour au site
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer-area area-padding-top mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-sm-6 single-footer-widget">
                    <h4>Medcare Medical</h4>
                    <ul>
                        <li><a href="../frontoffice/home/index.php">Accueil</a></li>
                        <li><a href="../frontoffice/templete/about-us.html">À propos</a></li>
                        <li><a href="../frontoffice/templete/department.html">Services</a></li>
                        <li><a href="../frontoffice/templete/contact.html">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-sm-6 single-footer-widget">
                    <h4>Administration</h4>
                    <ul>
                        <li><a href="admin-dashboard.php">Dashboard</a></li>
                        <li><a href="admin-users.php">Utilisateurs</a></li>
                        <li><a href="gestion-rendezvous.php">Rendez-vous</a></li>
                        <li><a href="#">Rapports</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-sm-6 single-footer-widget">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">Aide</a></li>
                        <li><a href="#">Contact Support</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-sm-6 single-footer-widget">
                    <h4>Contact</h4>
                    <ul>
                        <li><i class="ti-email"></i> medical@example.com</li>
                        <li><i class="ti-mobile"></i> +33 1 23 45 67 89</li>
                        <li><i class="ti-location-pin"></i> Paris, France</li>
                    </ul>
                </div>
            </div>
            <div class="row footer-bottom d-flex justify-content-between">
                <p class="col-lg-8 col-sm-12 footer-text m-0">
                    &copy; <?= date('Y') ?> Medcare Medical. Tous droits réservés.
                </p>
                <div class="col-lg-4 col-sm-12 footer-social">
                    <a href="#"><i class="ti-facebook"></i></a>
                    <a href="#"><i class="ti-twitter"></i></a>
                    <a href="#"><i class="ti-linkedin"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../assets/js/popper.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/stellar.js"></script>
    <script src="../assets/js/theme.js"></script>
    
    <script>
        // Activer les dropdowns de Bootstrap
        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
        var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl)
        });
        
        // Gérer le menu déroulant des rendez-vous
        document.getElementById('appointmentsMenu').addEventListener('show.bs.collapse', function () {
            this.previousElementSibling.querySelector('.fa-chevron-down').classList.add('fa-chevron-up');
            this.previousElementSibling.querySelector('.fa-chevron-down').classList.remove('fa-chevron-down');
        });
        
        document.getElementById('appointmentsMenu').addEventListener('hide.bs.collapse', function () {
            this.previousElementSibling.querySelector('.fa-chevron-up').classList.add('fa-chevron-down');
            this.previousElementSibling.querySelector('.fa-chevron-up').classList.remove('fa-chevron-up');
        });
        
        // Confirmation de déconnexion
        document.querySelectorAll('a[href*="logout"]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                    e.preventDefault();
                }
            });
        });

        // Validation du formulaire
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            const role = document.getElementById('role').value;
            const statut = document.getElementById('statut').value;
            
            if (!role || !statut) {
                e.preventDefault();
                alert('Veuillez sélectionner un rôle et un statut !');
                return false;
            }
            
            // Demander confirmation pour la désactivation
            if (statut === 'inactif') {
                if (!confirm('Êtes-vous sûr de vouloir désactiver ce compte ? L\'utilisateur ne pourra plus se connecter.')) {
                    e.preventDefault();
                    return false;
                }
            }
        });

        // Auto-dismiss alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.classList.contains('alert-danger')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
    </script>
</body>
</html>