<?php
session_start();
require_once __DIR__ .'/../../../controllers/AuthController.php'; 

$authController = new AuthController();
$isLoggedIn = $authController->isLoggedIn();
$currentUser = $isLoggedIn ? $authController->getCurrentUser() : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - MonSite</title>
 
</head>
<body>
  
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">MonSite</a>
            <div class="nav-links">
                <a href="index.php">Accueil</a>
                <?php if ($isLoggedIn): ?>
                    <a href="../auth/profile.php">Mon Profil</a>
                    <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                        <a href="../../backoffice/admin-dashboard.php">Administration</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="#features">Fonctionnalités</a>
                <?php endif; ?>
            </div>
            <div class="nav-links">
                <?php if ($isLoggedIn): ?>
                    <span style="color: #667eea; font-weight: 600;">
                        Bonjour, <?php echo htmlspecialchars($currentUser['prenom']); ?>!
                    </span>
                    
                     <a href="../../../controllers/logout.php" class="btn btn-secondary" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">Déconnexion</a>
                <?php else: ?>
                    <a href="../auth/sign-in.php" class="btn btn-secondary">Connexion</a>
                    <a href="../auth/sign-up.php" class="btn btn-primary">Inscription</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>


    <section class="hero">
        <div class="container">
            <?php if ($isLoggedIn): ?>
                <div class="user-welcome">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($currentUser['prenom'], 0, 1)); ?>
                    </div>
                    <h1>Content de vous revoir !</h1>
                    <p>Bienvenue de retour sur MonSite, <?php echo htmlspecialchars($currentUser['prenom']); ?>!</p>
                    <div class="cta-buttons">
                        <a href="../auth/profile.php" class="btn btn-primary">Mon Profil</a>
                        <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                            <a href="../../backoffice/admin-dashboard.php" class="btn btn-secondary">Dashboard Admin</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <h1>Bienvenue sur MonSite</h1>
                <p>Découvrez une plateforme innovante qui révolutionne votre façon de gérer vos données et interactions en ligne.</p>
                <div class="cta-buttons">
                    <a href="../auth/sign-up.php" class="btn btn-primary">Commencer gratuitement</a>
                    <a href="../auth/sign-in.php" class="btn btn-secondary">Se connecter</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="features" id="features">
        <div class="container">
            <h2 class="section-title">Pourquoi nous choisir ?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Sécurité Maximale</h3>
                    <p>Vos données sont protégées avec les dernières technologies de cryptage et de sécurité.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Performance Optimale</h3>
                    <p>Une plateforme rapide et réactive, conçue pour offrir la meilleure expérience utilisateur.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <h3>Interface Intuitive</h3>
                    <p>Une interface conviviale et facile à utiliser, même pour les débutants.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔄</div>
                    <h3>Mises à Jour Régulières</h3>
                    <p>Des améliorations constantes pour répondre à vos besoins évolutifs.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Responsive Design</h3>
                    <p>Accédez à votre compte depuis n'importe quel appareil, à tout moment.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🛡️</div>
                    <h3>Support 24/7</h3>
                    <p>Notre équipe de support est disponible pour vous aider à tout moment.</p>
                </div>
            </div>
        </div>
    </section>

    <section style="background: #f8f9fa; padding: 80px 2rem;">
        <div class="container" style="text-align: center;">
            <h2 style="font-size: 2.5rem; margin-bottom: 1rem; color: #333;">Prêt à commencer ?</h2>
            <p style="font-size: 1.2rem; margin-bottom: 2rem; color: #666; max-width: 600px; margin-left: auto; margin-right: auto;">
                Rejoignez des milliers d'utilisateurs satisfaits et découvrez comment notre plateforme peut transformer votre expérience en ligne.
            </p>
            <?php if (!$isLoggedIn): ?>
                <div class="cta-buttons">
                    <a href="../auth/signup.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 2rem;">
                        Créer un compte gratuit
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

   
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> MonSite. Tous droits réservés.</p>
            <p style="margin-top: 1rem; opacity: 0.8;">
                <a href="#" style="color: white; margin: 0 1rem;">Mentions légales</a>
                <a href="#" style="color: white; margin: 0 1rem;">Politique de confidentialité</a>
                <a href="#" style="color: white; margin: 0 1rem;">Contact</a>
            </p>
        </div>
    </footer>

    <script>
       
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

       
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.feature-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>