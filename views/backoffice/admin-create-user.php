<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/auth/sign-in.php');
    exit;
}

require_once __DIR__ . '/../../controllers/AdminController.php';
$adminController = new AdminController();

$error_message = null;
$success_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage des données avant traitement
    $post_data = array_map(function($value) {
        if (is_string($value)) {
            // Supprimer les balises HTML et PHP, et convertir les caractères spéciaux
            $value = strip_tags($value);
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            // Supprimer les espaces en début et fin
            $value = trim($value);
        }
        return $value;
    }, $_POST);
    
    $result = $adminController->manageUsers('create', $post_data);
    
    if ($result['success']) {
        $success_message = $result['message'];
        $_POST = [];
    } else {
        $error_message = $result['message'];
    }
}

// Fonction pour échapper les données affichées dans le formulaire
function escape_data($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Utilisateur - Medsense Medical</title>
    
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
        
        .form-control.invalid {
            border-color: var(--danger-color);
            box-shadow: 0 0 0 0.2rem rgba(230, 55, 87, 0.25);
        }
        
        .form-control.valid {
            border-color: var(--success-color);
        }
        
        .validation-feedback {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .validation-feedback.invalid {
            color: var(--danger-color);
        }
        
        .validation-feedback.valid {
            color: var(--success-color);
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
        
        .password-card {
            border-left: 4px solid var(--info-color);
        }
        
        .password-card .card-header {
            background-color: rgba(57, 175, 209, 0.05);
        }
        
        .security-alert {
            border-left: 4px solid var(--warning-color);
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
            <h3><i class="fas fa-hospital me-2"></i>medsense</h3>
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
            <h4 class="mb-0">Création d'Utilisateur</h4>
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
                            <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Ajouter un Nouvel Utilisateur</h4>
                            <a href="admin-users.php" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Retour à la liste
                            </a>
                        </div>
                        <div class="card-body">
                           
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

                            <div class="alert alert-warning security-alert mb-4">
                                <h6><i class="fas fa-shield-alt me-2"></i>Sécurité des données</h6>
                                <small>
                                    Toutes les données saisies sont automatiquement protégées contre les injections HTML et les scripts malveillants.
                                    Les caractères spéciaux sont échappés pour garantir la sécurité du système.
                                </small>
                            </div>

                            <form method="POST" id="createUserForm" novalidate>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nom" name="nom" 
                                                   value="<?= escape_data($_POST['nom'] ?? '') ?>" 
                                                   pattern="[A-Za-zÀ-ÿ\s\-']{2,50}" 
                                                   title="Le nom doit contenir entre 2 et 50 caractères alphabétiques" 
                                                   required>
                                            <div class="form-text">2 à 50 caractères alphabétiques uniquement</div>
                                            <div class="validation-feedback" id="nom-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="prenom" name="prenom" 
                                                   value="<?= escape_data($_POST['prenom'] ?? '') ?>" 
                                                   pattern="[A-Za-zÀ-ÿ\s\-']{2,50}" 
                                                   title="Le prénom doit contenir entre 2 et 50 caractères alphabétiques" 
                                                   required>
                                            <div class="form-text">2 à 50 caractères alphabétiques uniquement</div>
                                            <div class="validation-feedback" id="prenom-feedback"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= escape_data($_POST['email'] ?? '') ?>" 
                                           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" 
                                           title="Veuillez entrer une adresse email valide" 
                                           required>
                                    <div class="form-text">L'adresse email sera utilisée pour la connexion</div>
                                    <div class="validation-feedback" id="email-feedback"></div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="dateNaissance" class="form-label">Date de Naissance</label>
                                            <input type="date" class="form-control" id="dateNaissance" name="dateNaissance" 
                                                   value="<?= escape_data($_POST['dateNaissance'] ?? '') ?>"
                                                   max="<?= date('Y-m-d', strtotime('-18 years')) ?>"
                                                   title="L'utilisateur doit être majeur (18 ans minimum)">
                                            <div class="form-text">Âge minimum : 18 ans</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="adresse" class="form-label">Adresse</label>
                                            <input type="text" class="form-control" id="adresse" name="adresse" 
                                                   value="<?= escape_data($_POST['adresse'] ?? '') ?>"
                                                   maxlength="255"
                                                   title="Maximum 255 caractères">
                                            <div class="form-text">Maximum 255 caractères</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="role" class="form-label">Rôle <span class="text-danger">*</span></label>
                                            <select class="form-select" id="role" name="role" required>
                                                <option value="">Sélectionner un rôle</option>
                                                <option value="user" <?= (($_POST['role'] ?? '') === 'user') ? 'selected' : '' ?>>Utilisateur</option>
                                                <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Administrateur</option>
                                                <option value="moderator" <?= (($_POST['role'] ?? '') === 'moderator') ? 'selected' : '' ?>>Modérateur</option>
                                            </select>
                                            <div class="validation-feedback" id="role-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                                            <select class="form-select" id="statut" name="statut" required>
                                                <option value="">Sélectionner un statut</option>
                                                <option value="actif" <?= (($_POST['statut'] ?? '') === 'actif') ? 'selected' : '' ?>>Actif</option>
                                                <option value="inactif" <?= (($_POST['statut'] ?? '') === 'inactif') ? 'selected' : '' ?>>Inactif</option>
                                            </select>
                                            <div class="validation-feedback" id="statut-feedback"></div>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="card mt-4 password-card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-lock me-2"></i>Mot de passe <span class="text-danger">*</span></h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <small>
                                                <i class="fas fa-info-circle"></i>
                                                Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial
                                            </small>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="mot_de_passe" class="form-label">Mot de passe</label>
                                                    <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" 
                                                           pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                                                           title="Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial"
                                                           required>
                                                    <div class="form-text">8 caractères minimum avec majuscule, minuscule, chiffre et caractère spécial</div>
                                                    <div class="validation-feedback" id="password-feedback"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="confirm_mot_de_passe" class="form-label">Confirmer le mot de passe</label>
                                                    <input type="password" class="form-control" id="confirm_mot_de_passe" 
                                                           name="confirm_mot_de_passe" required>
                                                    <div class="validation-feedback" id="confirm-password-feedback"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-user-plus me-1"></i> Créer l'utilisateur
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary" id="resetBtn">Réinitialiser</button>
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

        // Validation en temps réel
        const form = document.getElementById('createUserForm');
        const inputs = form.querySelectorAll('input, select');
        
        // Expressions régulières pour la validation
        const patterns = {
            nom: /^[A-Za-zÀ-ÿ\s\-']{2,50}$/,
            prenom: /^[A-Za-zÀ-ÿ\s\-']{2,50}$/,
            email: /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/,
            mot_de_passe: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/
        };
        
        // Fonction de validation
        function validateField(field) {
            const value = field.value.trim();
            const id = field.id;
            const feedback = document.getElementById(`${id}-feedback`);
            
            // Réinitialiser les styles
            field.classList.remove('invalid', 'valid');
            feedback.textContent = '';
            feedback.className = 'validation-feedback';
            
            // Validation des champs requis
            if (field.hasAttribute('required') && !value) {
                field.classList.add('invalid');
                feedback.textContent = 'Ce champ est obligatoire';
                feedback.classList.add('invalid');
                return false;
            }
            
            // Validation par pattern
            if (field.hasAttribute('pattern') && value) {
                const pattern = new RegExp(field.getAttribute('pattern'));
                if (!pattern.test(value)) {
                    field.classList.add('invalid');
                    feedback.textContent = field.getAttribute('title');
                    feedback.classList.add('invalid');
                    return false;
                }
            }
            
            // Validation spéciale pour la confirmation du mot de passe
            if (id === 'confirm_mot_de_passe' && value) {
                const password = document.getElementById('mot_de_passe').value;
                if (value !== password) {
                    field.classList.add('invalid');
                    feedback.textContent = 'Les mots de passe ne correspondent pas';
                    feedback.classList.add('invalid');
                    return false;
                }
            }
            
            // Si tout est valide
            if (value) {
                field.classList.add('valid');
                feedback.textContent = '✓ Champ valide';
                feedback.classList.add('valid');
            }
            
            return true;
        }
        
        // Événements de validation en temps réel
        inputs.forEach(input => {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => {
                // Nettoyer les classes de validation pendant la saisie
                input.classList.remove('invalid', 'valid');
                document.getElementById(`${input.id}-feedback`).textContent = '';
            });
        });
        
        // Validation du formulaire à la soumission
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Valider tous les champs
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                // Afficher une alerte générale
                const invalidFields = form.querySelectorAll('.invalid');
                if (invalidFields.length > 0) {
                    alert('Veuillez corriger les erreurs dans le formulaire avant de soumettre.');
                    invalidFields[0].focus();
                }
            }
        });
        
        // Réinitialisation du formulaire
        document.getElementById('resetBtn').addEventListener('click', function() {
            // Réinitialiser les styles de validation
            inputs.forEach(input => {
                input.classList.remove('invalid', 'valid');
                document.getElementById(`${input.id}-feedback`).textContent = '';
            });
        });
        
        // Protection contre la copie/collage de HTML
        document.addEventListener('paste', function(e) {
            const target = e.target;
            if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') {
                // Récupérer le texte brut sans HTML
                const text = (e.clipboardData || window.clipboardData).getData('text/plain');
                e.preventDefault();
                
                // Insérer le texte nettoyé
                const start = target.selectionStart;
                const end = target.selectionEnd;
                target.value = target.value.substring(0, start) + text + target.value.substring(end);
                target.selectionStart = target.selectionEnd = start + text.length;
                
                // Déclencher la validation
                validateField(target);
            }
        });

        // Auto-dismiss alerts
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