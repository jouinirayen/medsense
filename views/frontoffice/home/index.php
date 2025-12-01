<?php
session_start();
require_once __DIR__ . '/../../../controllers/AuthController.php'; 

$authController = new AuthController();
$isLoggedIn = $authController->isLoggedIn();

if ($isLoggedIn) {
    $currentUser = $authController->getCurrentUser();
    // Si $currentUser est un objet, le convertir en tableau
    $currentUserArray = $currentUser ? (method_exists($currentUser, 'toArray') ? $currentUser->toArray() : (array)$currentUser) : null;
} else {
    $currentUserArray = null;
    $currentUser = null; // Ajouter cette ligne pour cohérence
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="img/favicon.png" type="image/png">
    <title>Medcare Medical</title>
    
    <link rel="stylesheet" href="../../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/css/flaticon.css">
    <link rel="stylesheet" href="../../assets/vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../../assets/vendors/owl-carousel/owl.carousel.min.css">
    <link rel="stylesheet" href="../../assets/vendors/animate-css/animate.css">
    
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
</head>
<body>

<header class="header_area">
    <!-- 🔹 TOP BAR -->
    <div class="top_menu row m0">
        <div class="container">
            <div class="float-left">
                <a class="dn_btn" href="mailto:medical@example.com">
                    <i class="ti-email"></i> medical@example.com
                </a>
                <span class="dn_btn">
                    <i class="ti-location-pin"></i> Find our Location
                </span>
            </div>

            <div class="float-right d-flex align-items-center">
                <!-- Social Media -->
                <ul class="list header_social">
                    <li><a href="#"><i class="ti-facebook"></i></a></li>
                    <li><a href="#"><i class="ti-twitter-alt"></i></a></li>
                    <li><a href="#"><i class="ti-linkedin"></i></a></li>
                    <li><a href="#"><i class="ti-skype"></i></a></li>
                    <li><a href="#"><i class="ti-vimeo-alt"></i></a></li>
                </ul>

                <!-- Auth -->
                <div class="auth-top ml-3">
                    <?php if ($isLoggedIn && $currentUserArray): ?>
                        <span style="color: #fff; margin-right: 15px;">
                            <i class="ti-user"></i> Bonjour,
                            <?= htmlspecialchars($currentUserArray['prenom'] ?? $currentUserArray['user_prenom'] ?? 'Utilisateur'); ?>
                        </span>

                        <a href="../../../controllers/logout.php" style="color: #fff; text-decoration: underline;">
                            <i class="ti-power-off"></i> Déconnexion
                        </a>

                    <?php else: ?>
                        <a href="../auth/sign-in.php" style="color: #fff; margin-right: 10px;">
                            <i class="ti-lock"></i> Connexion
                        </a>

                        <a href="../auth/sign-up.php" style="color: #fff;">
                            <i class="ti-user"></i> Inscription
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 🔹 MAIN NAVBAR -->
    <div class="main_menu">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container">
                <!-- LOGO -->
                <a class="navbar-brand logo_h" href="#">
                    <img src="../../assets/img/logo.png" alt="logo" style="height: 120px;">
                </a>

                <!-- MOBILE TOGGLER -->
                <button class="navbar-toggler" type="button" data-toggle="collapse"
                        data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <!-- NAVIGATION LINKS -->
                <div class="collapse navbar-collapse offset" id="navbarSupportedContent">
                    <ul class="nav navbar-nav menu_nav ml-auto">
                        <li class="nav-item active"><a class="nav-link" href="index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="../templete/about-us.html">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="../templete/department.html">Department</a></li>
                        <li class="nav-item"><a class="nav-link" href="../templete/doctors.html">Doctors</a></li>

                        <!-- Blog menu -->
                        <li class="nav-item submenu dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button"
                               aria-haspopup="true" aria-expanded="false">Blog</a>
                            <ul class="dropdown-menu">
                                <li class="nav-item"><a class="nav-link" href="../templete/blog.html">Blog</a></li>
                                <li class="nav-item"><a class="nav-link" href="../templete/single-blog.html">Blog Details</a></li>
                                <li class="nav-item"><a class="nav-link" href="../templete/element.html">Element</a></li>
                            </ul>
                        </li>

                        <li class="nav-item"><a class="nav-link" href="contact.html">Contact</a></li>

                        <!-- COMPTE UTILISATEUR -->
                        <?php if ($isLoggedIn && $currentUserArray): ?>
                            <li class="nav-item submenu dropdown">
                                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button"
                                   aria-haspopup="true" aria-expanded="false">
                                    <i class="ti-user"></i> Mon Compte
                                </a>

                                <ul class="dropdown-menu">
                                    <li class="nav-item"><a class="nav-link" href="../auth/profile.php">Mon Profil</a></li>
                                    <li class="nav-item"><a class="nav-link" href="../appointments/">Mes Rendez-vous</a></li>

                                    <?php if (($currentUserArray['role'] ?? $currentUserArray['user_role'] ?? '') === 'admin'): ?>
                                        <li class="nav-item">
                                            <a class="nav-link" href="../../backoffice/admin-dashboard.php">Administration</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</header>

<section class="banner-area d-flex align-items-center">
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <?php if ($isLoggedIn && $currentUserArray): ?>
                    <div class="user-welcome mb-4">
                        <h1>Content de vous revoir, <?php echo htmlspecialchars($currentUserArray['prenom'] ?? $currentUserArray['user_prenom'] ?? 'Utilisateur'); ?>!</h1>
                        <p>Bienvenue de retour sur Medcare Medical. Nous sommes heureux de vous revoir.</p>
                        <div class="cta-buttons mt-3">
                            <a href="../auth/profile.php" class="main_btn">Mon Profil</a>
                            <?php if (($currentUserArray['role'] ?? $currentUserArray['user_role'] ?? '') === 'admin'): ?>
                                <a href="../../backoffice/admin-dashboard.php" class="main_btn_light">Dashboard Admin</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <h1>Making Health<br>Care Better Together</h1>
                    <p>Also you dry creeping beast multiply fourth abundantly our itsel signs bring our. Won form living. Whose dry you seasons divide given gathering great in whose you'll greater let livein form beast sinthete better together these place absolute right.</p>
                    <div class="cta-buttons">
                        <a href="../auth/sign-up.php" class="main_btn">Make an Appointment</a>
                        <a href="../templete/department.html" class="main_btn_light">View Department</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php if (!$isLoggedIn): ?>
<section class="cta-section" style="background: linear-gradient(90deg, #071551ff 0%, #7a9edbff 100%); padding: 80px 0; color: white;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 style="color: white; margin-bottom: 10px;">Prêt à prendre soin de votre santé ?</h3>
                <p style="color: white; opacity: 0.9; margin-bottom: 0;">Rejoignez nos patients satisfaits et bénéficiez de soins de qualité</p>
            </div>
            <div class="col-lg-4 text-lg-right">
                <a href="../auth/sign-up.php" class="main_btn" style="background: white; color: #99d8dcff;">Créer un compte gratuit</a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="appointment-area">
    <div class="container">
        <div class="appointment-inner">
            <div class="row">
                <div class="col-sm-12 col-lg-5 offset-lg-1">
                    <h3>Have Some Questions?</h3>
                </div>
                <div class="col-lg-5">
                    <div class="appointment-form">
                        <h3>Make an Appointment</h3>
                        <?php if ($isLoggedIn && $currentUserArray): ?>
                            <form action="#">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" value="<?php echo htmlspecialchars(($currentUserArray['prenom'] ?? $currentUserArray['user_prenom'] ?? '') . ' ' . ($currentUserArray['nom'] ?? $currentUserArray['user_nom'] ?? '')); ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" value="<?php echo htmlspecialchars($currentUserArray['email'] ?? $currentUserArray['user_email'] ?? ''); ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Message</label>
                                    <textarea name="message" cols="20" rows="7" placeholder="Describe your medical needs" required></textarea>
                                </div>
                                <a href="#" class="main_btn">Make an Appointment</a>
                            </form>
                        <?php else: ?>
                            <div class="text-center">
                                <p>Vous devez être connecté pour prendre rendez-vous</p>
                                <a href="../auth/sign-in.php" class="main_btn">Se connecter</a>
                                <a href="../auth/sign-up.php" class="main_btn_light">S'inscrire</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
   

    
    <footer class="footer-area area-padding-top">
        <div class="container">
            <div class="row">
                <div class="col-lg-2 col-sm-6 single-footer-widget">
                    <h4>Top Products</h4>
                    <ul>
                        <li><a href="#">Managed Website</a></li>
                        <li><a href="#">Manage Reputation</a></li>
                        <li><a href="#">Power Tools</a></li>
                        <li><a href="#">Marketing Service</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-sm-6 single-footer-widget">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#">Jobs</a></li>
                        <li><a href="#">Brand Assets</a></li>
                        <li><a href="#">Investor Relations</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-sm-6 single-footer-widget">
                    <h4>Features</h4>
                    <ul>
                        <li><a href="#">Jobs</a></li>
                        <li><a href="#">Brand Assets</a></li>
                        <li><a href="#">Investor Relations</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-sm-6 single-footer-widget">
                    <h4>Resources</h4>
                    <ul>
                        <li><a href="#">Guides</a></li>
                        <li><a href="#">Research</a></li>
                        <li><a href="#">Experts</a></li>
                        <li><a href="#">Agencies</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-6 single-footer-widget">
                    <h4>Newsletter</h4>
                    <p>You can trust us. we only send promo offers,</p>
                    <div class="form-wrap" id="mc_embed_signup">
                        <form target="_blank" action="https://spondonit.us12.list-manage.com/subscribe/post?u=1462626880ade1ac87bd9c93a&amp;id=92a4423d01"
                        method="get" class="form-inline">
                        <input class="form-control" name="EMAIL" placeholder="Your Email Address" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Your Email Address'"
                        required="" type="email" />
                        <button class="click-btn btn btn-default">
                            <i class="ti-arrow-right"></i>
                        </button>
                        <div style="position: absolute; left: -5000px;">
                            <input name="b_36c4fd991d266f23781ded980_aefe40901a" tabindex="-1" value="" type="text" />
                        </div>

                        <div class="info"></div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row footer-bottom d-flex justify-content-between">
            <p class="col-lg-8 col-sm-12 footer-text m-0">
                
Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved | This template is made with <i class="fa fa-heart" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Colorlib</a>

            </p>
            <div class="col-lg-4 col-sm-12 footer-social">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-dribbble"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
            </div>
        </div>
    </div>
</footer>
<script src="js/jquery-2.2.4.min.js"></script>
<script src="js/popper.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/stellar.js"></script>
<script src="vendors/owl-carousel/owl.carousel.min.js"></script>
<script src="js/jquery.ajaxchimp.min.js"></script>
<script src="js/waypoints.min.js"></script>
<script src="js/mail-script.js"></script>
<script src="js/contact.js"></script>
<script src="js/jquery.form.js"></script>
<script src="js/jquery.validate.min.js"></script>
<script src="js/mail-script.js"></script>
<script src="js/theme.js"></script>
</body>
</html>