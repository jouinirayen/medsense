<?php
session_start();
require_once __DIR__ . '/../../../controllers/AuthController.php';


$current_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$current_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

$old_ip = $_SESSION['ia_last_ip'] ?? null;
$old_agent = $_SESSION['ia_last_agent'] ?? null;

$alerts = [];
if ($old_ip && $old_ip !== $current_ip) {
    $alerts[] = "⚠️ Nouvelle adresse IP détectée : $current_ip";
}
if ($old_agent && $old_agent !== $current_agent) {
    $alerts[] = "⚠️ Nouveau navigateur détecté";
}

$_SESSION['ia_last_ip'] = $current_ip;
$_SESSION['ia_last_agent'] = $current_agent;
$ia_security_alert = $alerts ? implode("<br>", $alerts) : null;

$authController = new AuthController();
$isLoggedIn = $authController->isLoggedIn();

if ($isLoggedIn) {
    $currentUser = $authController->getCurrentUser();
    $currentUserArray = $currentUser ? (method_exists($currentUser, 'toArray') ? $currentUser->toArray() : (array)$currentUser) : null;
} else {
    $currentUserArray = null;
    $currentUser = null;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Medsense Medical - Votre plateforme de santé en ligne. Prenez rendez-vous avec des médecins qualifiés, gérez vos consultations et accédez à des ressources médicales.">
    <meta name="keywords" content="médecin, rendez-vous médical, santé en ligne, consultation, médical">
    
    <title>Medsense Medical - Plateforme de santé en ligne</title>
    <link rel="icon" href="../../assets/img/favicon.png" type="image/png">
    
    
    <link rel="stylesheet" href="../../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/chatbot.css">
    <link rel="stylesheet" href="../../assets/vendors/fontawesome/css/all.min.css">
    
   
    <style>
        :root {
            --medical-blue: #1a73e8;
            --medical-light-blue: #e8f0fe;
            --medical-dark-blue: #0d47a1;
            --medical-teal: #007c91;
            --medical-cyan: #00bcd4;
            --medical-light-cyan: #b2ebf2;
            --secondary-color: #5f6368;
            --success-color: #4caf50;
            --info-color: #2196f3;
            --warning-color: #ff9800;
            --danger-color: #f44336;
            --light-color: #f8f9fa;
            --dark-color: #202124;
            --card-radius: 12px;
            --shadow-light: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-medium: 0 6px 15px rgba(0, 0, 0, 0.07);
            --shadow-strong: 0 10px 25px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
      
        .security-alert {
            background: #fff5f5;
            color: #c53030;
            border-left: 4px solid var(--danger-color);
            padding: 15px 20px;
            margin: 0 auto 15px;
            border-radius: 0 8px 8px 0;
            font-size: 14px;
            max-width: 100%;
            box-shadow: var(--shadow-light);
            animation: fadeInUp 0.6s ease-out;
        }
        
        .ms-header {
            background: #ffffff;
            box-shadow: var(--shadow-medium);
            padding: 15px 40px;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: var(--transition);
            border-bottom: 3px solid var(--medical-blue);
        }
        
        .ms-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .ms-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .ms-logo {
            height: 55px;
            transition: var(--transition);
        }
        
        .ms-logo:hover {
            transform: scale(1.05);
        }
        
        .ms-brand {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--medical-blue) 0%, var(--medical-teal) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .ms-right {
            display: flex;
            align-items: center;
            gap: 25px;
        }
        
        .ms-right a {
            text-decoration: none;
            color: var(--dark-color);
            font-weight: 500;
            font-size: 15px;
            padding: 10px 0;
            position: relative;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .ms-right a:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: var(--medical-blue);
            transition: var(--transition);
        }
        
        .ms-right a:hover {
            color: var(--medical-blue);
        }
        
        .ms-right a:hover:after {
            width: 100%;
        }
        
        .ms-right a i {
            font-size: 14px;
        }
        
        .ms-btn {
            background: linear-gradient(135deg, var(--medical-blue) 0%, var(--medical-dark-blue) 100%);
            color: white !important;
            padding: 12px 28px;
            border-radius: var(--card-radius);
            font-weight: 600;
            border: none;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .ms-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(26, 115, 232, 0.25);
        }
        
        .ms-btn:hover:after {
            width: 0;
        }
        
        .ms-logout {
            color: var(--danger-color) !important;
        }
        
        .ms-logout:hover {
            color: #c0392b !important;
        }
        
       
        .ms-hero {
            background: linear-gradient(135deg, var(--medical-light-blue) 0%, var(--medical-light-cyan) 100%);
            padding: 120px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .ms-hero:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="white" opacity="0.05"/></svg>');
            background-size: cover;
        }
        
        .ms-hero-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        .ms-hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--medical-dark-blue);
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .ms-hero-subtitle {
            font-size: 1.25rem;
            color: var(--secondary-color);
            margin-bottom: 50px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }
        
        .ms-hero-badges {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 50px;
            flex-wrap: wrap;
        }
        
        .ms-badge {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 25px;
            border-radius: 30px;
            font-size: 15px;
            color: var(--medical-dark-blue);
            box-shadow: var(--shadow-medium);
            display: flex;
            align-items: center;
            gap: 12px;
            transition: var(--transition);
            border: 2px solid var(--medical-light-blue);
        }
        
        .ms-badge:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-strong);
            border-color: var(--medical-blue);
        }
        
        .ms-badge i {
            color: var(--medical-blue);
            font-size: 20px;
        }

      
        .appointment-section {
            padding: 100px 20px;
            background: var(--light-color);
        }
        
        .appointment-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .appointment-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .appointment-header h2 {
            font-size: 2.5rem;
            color: var(--medical-dark-blue);
            margin-bottom: 15px;
        }
        
        .appointment-header p {
            color: var(--secondary-color);
            font-size: 1.125rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .appointment-card {
            background: white;
            border-radius: var(--card-radius);
            padding: 40px;
            box-shadow: var(--shadow-strong);
            max-width: 800px;
            margin: 0 auto;
            border-top: 4px solid var(--medical-blue);
        }
        
        .appointment-card h3 {
            color: var(--medical-dark-blue);
            margin-bottom: 30px;
            font-size: 1.75rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .appointment-card h3 i {
            color: var(--medical-blue);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark-color);
            font-weight: 600;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: var(--transition);
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--medical-blue);
            box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
        }
        
        .form-group input[readonly] {
            background: var(--light-color);
            cursor: not-allowed;
        }
        
      
        .cta-section {
            background: linear-gradient(135deg, var(--medical-blue) 0%, var(--medical-teal) 100%);
            padding: 100px 20px;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .cta-section:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="white" opacity="0.05"/></svg>');
            background-size: cover;
        }
        
        .cta-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        .cta-content h3 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .cta-content p {
            font-size: 1.125rem;
            opacity: 0.9;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-primary, .btn-secondary {
            padding: 15px 35px;
            border-radius: var(--card-radius);
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: white;
            color: var(--medical-blue);
            box-shadow: var(--shadow-medium);
        }
        
        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-strong);
            background: var(--light-color);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
            transform: translateY(-3px);
        }
        
        /* ============ FOOTER CORPORATE ============ */
        .ms-footer {
            background: var(--medical-dark-blue);
            color: white;
            padding: 60px 20px 30px;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }
        
        .footer-section h4 {
            font-size: 18px;
            margin-bottom: 20px;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .footer-section h4 i {
            color: var(--medical-cyan);
        }
        
        .footer-section p {
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .footer-section a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            transition: var(--transition);
        }
        
        .footer-section a:hover {
            color: white;
            transform: translateX(5px);
        }
        
        .footer-section a i {
            width: 20px;
        }
        
        .footer-bottom {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        
     
        .role-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            gap: 5px;
        }
        
        .role-badge.user {
            background: rgba(26, 115, 232, 0.1);
            color: var(--medical-blue);
            border: 1px solid rgba(26, 115, 232, 0.3);
        }
        
        .role-badge.admin {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(244, 67, 54, 0.3);
        }
        
   
        @media (max-width: 992px) {
            .ms-hero-title {
                font-size: 2.8rem;
            }
            
            .ms-header {
                padding: 15px 20px;
            }
            
            .ms-right {
                gap: 15px;
            }
        }
        
        @media (max-width: 768px) {
            .ms-header {
                padding: 15px 20px;
            }
            
            .ms-nav {
                flex-direction: column;
                gap: 15px;
            }
            
            .ms-left {
                justify-content: center;
                width: 100%;
            }
            
            .ms-right {
                flex-wrap: wrap;
                justify-content: center;
                width: 100%;
                gap: 15px;
            }
            
            .ms-hero-title {
                font-size: 2.2rem;
            }
            
            .ms-hero-subtitle {
                font-size: 1.1rem;
            }
            
            .ms-hero-badges {
                flex-direction: column;
                align-items: center;
            }
            
            .ms-badge {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
            
            .appointment-card {
                padding: 25px;
            }
            
            .appointment-header h2 {
                font-size: 2rem;
            }
            
            .cta-content h3 {
                font-size: 2rem;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-primary, .btn-secondary {
                width: 100%;
                max-width: 300px;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .footer-section a {
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .ms-hero-title {
                font-size: 1.8rem;
            }
            
            .ms-hero {
                padding: 80px 20px;
            }
            
            .ms-btn {
                padding: 10px 20px;
                font-size: 14px;
            }
            
            .appointment-section {
                padding: 60px 20px;
            }
            
            .cta-section {
                padding: 60px 20px;
            }
        }
    </style>
</head>
<body>
    <?php if (!empty($ia_security_alert)): ?>
    <div class="security-alert">
        <strong><i class="fas fa-shield-alt"></i> Sécurité :</strong><br>
        <?= $ia_security_alert ?>
    </div>
    <?php endif; ?>
    

    <header class="ms-header animate-fade-in-up">
        <nav class="ms-nav">
            <div class="ms-left">
                <a class="navbar-brand logo_h" href="../home/index.php">
                    <img src="../../assets/img/logo.png" alt="Logo Medsense Medical" class="ms-logo">
                </a>
                <div class="ms-brand">Medsense Medical</div>
            </div>

            <div class="ms-right">
                <a href="index.php"><i class="fas fa-home"></i> Accueil</a>
                <?php if ($isLoggedIn && $currentUserArray): ?>
                    <a href="../front.php/"><i class="fas fa-calendar-check"></i> Mes Rendez-vous</a>
                    <a href="blog.php"><i class="fas fa-blog"></i> Blog</a>
                    <a href="reclamations.php"><i class="fas fa-comment-alt"></i> Réclamations</a>
                    <a href="../auth/profile.php"><i class="fas fa-user"></i> Mon compte</a>

                    <?php if (($currentUserArray['role'] ?? $currentUserArray['user_role']) === 'admin'): ?>
                        <a href="../../backoffice/admin-dashboard.php" class="role-badge admin">
                            <i class="fas fa-crown"></i> Admin
                        </a>
                    <?php endif; ?>

                    <a href="../../../controllers/logout.php" class="ms-logout">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>

                <?php else: ?>
                    <a href="../auth/sign-in.php"><i class="fas fa-sign-in-alt"></i> Connexion</a>
                    <a href="../auth/select-role.php" class="ms-btn">
                        <i class="fas fa-user-plus"></i> Inscription
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
 
    <section class="ms-hero animate-fade-in-up">
        <div class="ms-hero-container">
            <h1 class="ms-hero-title">
                <i class="fas fa-heartbeat"></i> Bienvenue chez Medsense Medical
            </h1>
            <p class="ms-hero-subtitle">
                Votre santé, notre priorité. Découvrez nos services médicaux de qualité 
                et prenez rendez-vous facilement avec nos médecins certifiés.
            </p>
            
            <div class="ms-hero-badges">
                <div class="ms-badge">
                    <i class="fas fa-bolt"></i> Prise de RDV rapide
                </div>
                <div class="ms-badge">
                    <i class="fas fa-shield-alt"></i> 100% sécurisé
                </div>
                <div class="ms-badge">
                    <i class="fas fa-check-circle"></i> Médecins vérifiés
                </div>
                <div class="ms-badge">
                    <i class="fas fa-clock"></i> Disponible 24h/24
                </div>
            </div>
        </div>
    </section>
    
    
    <section class="appointment-section">
        <div class="appointment-container">
            <div class="appointment-header animate-fade-in-up">
                <h2><i class="fas fa-calendar-plus"></i> Prenez rendez-vous en ligne</h2>
                <p>Simplifiez-vous la vie avec notre système de prise de rendez-vous en ligne</p>
            </div>
            
            <div class="appointment-card animate-fade-in-up">
                <h3><i class="fas fa-stethoscope"></i> Demande de consultation</h3>
                
                <?php if ($isLoggedIn && $currentUserArray): ?>
                    <form action="#" method="POST">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Nom complet</label>
                            <input type="text" value="<?php echo htmlspecialchars(($currentUserArray['prenom'] ?? $currentUserArray['user_prenom'] ?? '') . ' ' . ($currentUserArray['nom'] ?? $currentUserArray['user_nom'] ?? '')); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Adresse email</label>
                            <input type="email" value="<?php echo htmlspecialchars($currentUserArray['email'] ?? $currentUserArray['user_email'] ?? ''); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-file-medical"></i> Type de consultation</label>
                            <select name="consultation_type" required>
                                <option value="">Sélectionnez...</option>
                                <option value="general">Médecine générale</option>
                                <option value="specialist">Spécialiste</option>
                                <option value="urgence">Urgence</option>
                                <option value="suivi">Suivi médical</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar-day"></i> Date souhaitée</label>
                            <input type="date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-comment-medical"></i> Message (optionnel)</label>
                            <textarea name="message" rows="4" placeholder="Décrivez brièvement le motif de votre consultation..."></textarea>
                        </div>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-paper-plane"></i> Prendre rendez-vous
                        </button>
                    </form>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px 20px;">
                        <h4 style="color: var(--medical-dark-blue); margin-bottom: 20px;">
                            <i class="fas fa-lock"></i> Connectez-vous pour prendre rendez-vous
                        </h4>
                        <p style="color: var(--secondary-color); margin-bottom: 30px;">
                            Pour accéder à notre service de prise de rendez-vous, veuillez vous connecter ou créer un compte.
                        </p>
                        <div class="cta-buttons">
                            <a href="../auth/sign-in.php" class="btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Se connecter
                            </a>
                            <a href="../auth/select-role.php" class="btn-secondary">
                                <i class="fas fa-user-plus"></i> Créer un compte
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    
    <?php if (!$isLoggedIn): ?>
    <section class="cta-section animate-fade-in-up">
        <div class="cta-content">
            <h3>Rejoignez notre communauté de patients satisfaits</h3>
            <p>
                Accédez à tous nos services médicaux en ligne. Gérez vos rendez-vous, 
                consultez vos historiques médicaux et bénéficiez d'un suivi personnalisé.
            </p>
            <div class="cta-buttons">
                <a href="../auth/select-role.php" class="btn-primary">
                    <i class="fas fa-user-plus"></i> S'inscrire gratuitement
                </a>
                <a href="../auth/sign-in.php" class="btn-secondary">
                    <i class="fas fa-sign-in-alt"></i> Déjà inscrit ? Se connecter
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    
    <footer class="ms-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4><i class="fas fa-heartbeat"></i> Medsense Medical</h4>
                <p>
                    Votre partenaire santé en ligne. Des consultations médicales 
                    accessibles où que vous soyez.
                </p>
                <div style="display: flex; gap: 15px; margin-top: 15px;">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4><i class="fas fa-link"></i> Liens rapides</h4>
                <a href="index.php"><i class="fas fa-home"></i> Accueil</a>
                <a href="blog.php"><i class="fas fa-blog"></i> Blog médical</a>
                <a href="reclamations.php"><i class="fas fa-comment-alt"></i> Réclamations</a>
                <a href="../appointments/"><i class="fas fa-calendar-check"></i> Prendre RDV</a>
            </div>
            
            <div class="footer-section">
                <h4><i class="fas fa-address-card"></i> Contact</h4>
                <a href="mailto:contact@medsense.com"><i class="fas fa-envelope"></i> contact@medsense.com</a>
                <a href="tel:+33123456789"><i class="fas fa-phone"></i> 01 23 45 67 89</a>
                <p>
                    <i class="fas fa-clock"></i> Lundi - Vendredi : 8h-20h<br>
                    <i class="fas fa-clock"></i> Samedi : 9h-18h
                </p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Medsense Medical. Tous droits réservés. | 
            <a href="#" style="color: rgba(255, 255, 255, 0.6); text-decoration: none;">Mentions légales</a> | 
            <a href="#" style="color: rgba(255, 255, 255, 0.6); text-decoration: none;">Politique de confidentialité</a></p>
        </div>
    </footer>

    
    <button class="chatbot-btn" id="chatbot-btn" title="Assistant Medsense AI">
        <i class="fas fa-robot"></i>
    </button>

    <div class="chatbot-box" id="chatbot-box">
        <div class="chatbot-header">
            <h4><i class="fas fa-heartbeat"></i> Medsense AI Assistant</h4>
            <button class="close-chat" id="close-chat" title="Fermer">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="chat-body" id="chat-body">
            <div class="bot-msg">
                <strong>👋 Bonjour ! Je suis Medsense AI, votre assistant médical virtuel.</strong> 🤖<br><br>
                <strong>Je peux vous aider avec :</strong><br>
                • <strong>Rendez-vous</strong> 🩺 : Prise de RDV en ligne<br>
                • <strong>Réclamations</strong> 📝 : Dépôt et suivi<br>
                • <strong>Urgences</strong> 🚨 : Guide d'urgence médicale<br>
                • <strong>Blog</strong> 📚 : Articles médicaux<br>
                • <strong>Fonctionnalités</strong> 🌟 : Utilisation du site<br><br>
                <em>Comment puis-je vous aider aujourd'hui ?</em>
            </div>
        </div>
        
        <div class="suggestions">
            <h6><i class="fas fa-lightbulb"></i> Suggestions rapides :</h6>
            <div class="suggestion-chips">
                <div class="suggestion-chip" data-question="Comment prendre rendez-vous ?">Prendre RDV</div>
                <div class="suggestion-chip" data-question="Comment déposer une réclamation ?">Réclamation</div>
                <div class="suggestion-chip urgent-chip" data-question="Que faire en cas d'urgence ?">URGENCE</div>
                <div class="suggestion-chip" data-question="Comment accéder au blog ?">Blog médical</div>
                <div class="suggestion-chip" data-question="Quelles sont vos fonctionnalités ?">Fonctionnalités</div>
                <div class="suggestion-chip" data-question="Comment vous contacter ?">Contact</div>
            </div>
        </div>
        
        <div class="chat-input-area">
            <input type="text" class="chat-input" id="user-input" 
                   placeholder="Posez votre question sur Medsense..." autocomplete="off">
            <button class="send-btn" id="send-btn" title="Envoyer">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>

    
    <script src="../../assets/js/animations.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
       
        const chatbotBtn = document.getElementById('chatbot-btn');
        const chatbotBox = document.getElementById('chatbot-box');
        const closeChat = document.getElementById('close-chat');
        const sendBtn = document.getElementById('send-btn');
        const userInput = document.getElementById('user-input');
        const chatBody = document.getElementById('chat-body');
        const suggestionChips = document.querySelectorAll('.suggestion-chip');
        
        let isTyping = false;

        
        chatbotBtn.addEventListener('click', () => {
            chatbotBox.style.display = 'flex';
            setTimeout(() => chatbotBox.style.opacity = '1', 10);
            userInput.focus();
        });
       
        closeChat.addEventListener('click', () => {
            chatbotBox.style.opacity = '0';
            setTimeout(() => chatbotBox.style.display = 'none', 300);
        });
        
        document.addEventListener('click', (e) => {
            if (!chatbotBox.contains(e.target) && 
                e.target !== chatbotBtn && 
                !chatbotBtn.contains(e.target)) {
                chatbotBox.style.opacity = '0';
                setTimeout(() => chatbotBox.style.display = 'none', 300);
            }
        });

        
        suggestionChips.forEach(chip => {
            chip.addEventListener('click', () => {
                const question = chip.getAttribute('data-question');
                addMessage(question, 'user');
                processMessage(question);
            });
        });

       
        userInput.addEventListener('keypress', (e) => {
            if(e.key === 'Enter' && !e.shiftKey && !isTyping) {
                e.preventDefault();
                sendMessage();
            }
        });

        sendBtn.addEventListener('click', sendMessage);

        
        function sendMessage() {
            if (isTyping) return;
            
            const message = userInput.value.trim();
            if(!message) return;

            addMessage(message, 'user');
            userInput.value = '';
            
            processMessage(message);
        }

        function processMessage(message) {
            isTyping = true;
            
            const typingMsg = document.createElement('div');
            typingMsg.className = 'bot-msg typing';
            typingMsg.innerHTML = '<div class="typing-indicator"><span></span><span></span><span></span></div>';
            chatBody.appendChild(typingMsg);
            chatBody.scrollTop = chatBody.scrollHeight;

            setTimeout(() => {
                fetch('../../../controllers/chatbot.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'message=' + encodeURIComponent(message)
                })
                .then(response => {
                    if (!response.ok) throw new Error('Erreur réseau');
                    return response.json();
                })
                .then(data => {
                    typingMsg.remove();
                    
                    if (data.choices?.[0]?.message?.content) {
                        const botMessage = data.choices[0].message.content;
                        addMessage(botMessage, 'bot');
                    } else if (data.response) {
                        addMessage(data.response, 'bot');
                    } else {
                        addMessage('Je n\'ai pas compris votre demande. Pouvez-vous reformuler ?', 'bot');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    typingMsg.remove();
                    addMessage('⚠️ **Service temporairement indisponible**\n\nContactez-nous :\n📞 **Téléphone** : 01 23 45 67 89\n📧 **Email** : contact@medsense.com', 'bot');
                })
                .finally(() => {
                    isTyping = false;
                });
            }, 800);
        }

        
        function speak(text) {
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'fr-FR';
                utterance.pitch = 1;
                utterance.rate = 1;
                speechSynthesis.speak(utterance);
            }
        }

     
        function addMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `${sender}-msg`;

            let formattedText = text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>')
                .replace(/•/g, '•');

            messageDiv.innerHTML = formattedText;
            
            chatBody.appendChild(messageDiv);
            chatBody.scrollTop = chatBody.scrollHeight;

            if(sender === 'bot') {
                speak(text);
            }
        }
        
        
        const observerOptions = {
            threshold: 0.1
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        
        document.querySelectorAll('.appointment-card, .cta-content, .ms-hero-badges').forEach(el => {
            observer.observe(el);
        });
    });
    </script>
</body>
</html>