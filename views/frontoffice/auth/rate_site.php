<?php
session_start();
include_once '../../../controllers/AuthController.php';

$authController = new AuthController();

if (!$authController->isLoggedIn()) {
    header('Location: sign-in.php');
    exit;
}

$user = $authController->getCurrentUser();
$isAdmin = $user && $user->estAdmin();

// Traitement du formulaire d'évaluation
$rating_success = null;
$rating_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rating'])) {
    $rating = $_POST['rating'] ?? 0;
    $comment = $_POST['comment'] ?? '';
    
    if ($rating < 1 || $rating > 5) {
        $rating_error = "Veuillez sélectionner une note valide (1 à 5 étoiles)";
    } else {
        // Ici, vous devriez enregistrer l'évaluation dans la base de données
        // Pour l'instant, nous affichons juste un message de succès
        
        // Exemple d'enregistrement (à adapter selon votre structure de base de données):
        // $result = saveRating($user->getId(), $rating, $comment);
        
        $rating_success = "Merci pour votre évaluation de $rating étoile(s)!";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Évaluer le site - Medsense Medical</title>
    
    <!-- Mêmes styles CSS que profile.php -->
    <link rel="stylesheet" href="../../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/css/flaticon.css">
    <link rel="stylesheet" href="../../assets/vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    
    <style>
        /* Reprenez les mêmes styles CSS que profile.php */
        :root {
            --medical-blue: #1a73e8;
            --medical-light-blue: #e8f0fe;
            --medical-dark-blue: #0d47a1;
            --medical-teal: #007c91;
            --medical-cyan: #00bcd4;
            --sidebar-width: 280px;
            --card-radius: 12px;
            --shadow-light: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-medium: 0 6px 15px rgba(0, 0, 0, 0.07);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--medical-blue) 0%, var(--medical-dark-blue) 100%);
            color: white;
            padding: 0;
            z-index: 1000;
            box-shadow: var(--shadow-medium);
            transition: all 0.3s ease;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 25px;
            min-height: 100vh;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .rating-stars {
            font-size: 48px;
            color: #ffd700;
            margin: 20px 0;
        }
        
        .rating-stars .star {
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .rating-stars .star:hover {
            transform: scale(1.2);
        }
        
        .rating-stars input {
            display: none;
        }
        
        .rating-stars label {
            margin: 0 5px;
        }
        
        .card {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: var(--shadow-light);
            margin-bottom: 25px;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--medical-blue) 0%, var(--medical-teal) 100%);
            color: white;
            border-bottom: none;
            padding: 18px 25px;
            border-radius: var(--card-radius) var(--card-radius) 0 0 !important;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                transform: translateX(-100%);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
                width: 280px;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar (identique à profile.php) -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a class="navbar-brand logo_h" href="../home/index.php">
                <img src="../../assets/img/logo.png" alt="logo" style="height: 200px;">
            </a>
        </div>
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="../home/index.php">
                        <i class="fas fa-home"></i>
                        <span>Accueil</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">
                        <i class="fas fa-user"></i>
                        <span>Mon Profil</span>
                    </a>
                </li>
                <?php if ($isAdmin): ?>
                <li class="nav-item">
                    <a class="nav-link" href="../../backoffice/admin-dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tableau de Bord</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../../backoffice/admin-users.php">
                        <i class="fas fa-users"></i>
                        <span>Gestion Utilisateurs</span>
                    </a>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="../appointments/">
                        <i class="fas fa-calendar-check"></i>
                        <span>Mes Rendez-vous</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <!-- Lien vers la page d'évaluation -->
                <li class="nav-item">
                    <a class="nav-link active" href="rate_site.php">
                        <i class="fas fa-star"></i>
                        <span>Évaluer le site web</span>
                    </a>
                </li>
                
                <li class="nav-item mt-3">
                    <small class="text-uppercase text-muted ms-3">Compte</small>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../../../controllers/logout.php" 
                       onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0"><i class="fas fa-star me-2"></i>Évaluer le site web</h4>
                        </div>
                        <div class="card-body">
                            <?php if ($rating_success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle"></i>
                                    <?php echo $rating_success; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($rating_error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo $rating_error; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <p class="lead">Votre avis nous est précieux ! Aidez-nous à améliorer notre service en partageant votre expérience.</p>
                            
                            <form method="POST" action="">
                                <div class="mb-4 text-center">
                                    <h5>Note globale</h5>
                                    <div class="rating-stars" id="ratingStars">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                                            <label for="star<?php echo $i; ?>" class="star">
                                                <i class="fas fa-star"></i>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                    <small class="text-muted">Cliquez sur les étoiles pour attribuer une note</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Votre commentaire (optionnel)</label>
                                    <textarea class="form-control" id="comment" name="comment" rows="4" placeholder="Partagez votre expérience, vos suggestions d'amélioration..."></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Qu'avez-vous le plus apprécié ?</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="liked[]" value="design" id="design">
                                        <label class="form-check-label" for="design">
                                            Design et interface utilisateur
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="liked[]" value="navigation" id="navigation">
                                        <label class="form-check-label" for="navigation">
                                            Facilité de navigation
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="liked[]" value="functionality" id="functionality">
                                        <label class="form-check-label" for="functionality">
                                            Fonctionnalités proposées
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="liked[]" value="performance" id="performance">
                                        <label class="form-check-label" for="performance">
                                            Rapidité et performance
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" name="submit_rating" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Soumettre mon évaluation
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>
    
    <script>
        // Gestion du menu mobile
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('mobile-open');
        });

        // Animation des étoiles
        document.querySelectorAll('.star').forEach(star => {
            star.addEventListener('mouseover', function() {
                const rating = this.previousElementSibling.value;
                highlightStars(rating);
            });
            
            star.addEventListener('click', function() {
                const rating = this.previousElementSibling.value;
                highlightStars(rating);
            });
        });

        function highlightStars(rating) {
            const stars = document.querySelectorAll('.star');
            stars.forEach(star => {
                const starValue = star.previousElementSibling.value;
                if (starValue <= rating) {
                    star.style.color = '#ffd700';
                    star.querySelector('i').classList.remove('far');
                    star.querySelector('i').classList.add('fas');
                } else {
                    star.style.color = '#ddd';
                    star.querySelector('i').classList.remove('fas');
                    star.querySelector('i').classList.add('far');
                }
            });
        }
    </script>
</body>
</html>