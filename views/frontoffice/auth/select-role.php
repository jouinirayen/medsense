<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisissez votre profil - Medcare</title>
    <link rel="stylesheet" href="../../assets/vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/choice-style.css">
</head>
<body>
    <!-- Bulles décoratives -->
    <div class="water-bubble bubble-1"></div>
    <div class="water-bubble bubble-2"></div>
    <div class="water-bubble bubble-3"></div>
    
    <!-- Effet de vague -->
    <div class="wave"></div>
    <div class="wave"></div>
    
    <div class="container">
        <!-- En-tête -->
        <div class="header">
            <div class="logo">
                <i class="fas fa-tint logo-icon"></i>
                <span>Medcare Aqua</span>
            </div>
            <p class="subtitle">Choisissez votre profil pour accéder à nos services de santé en ligne</p>
        </div>
        
        <!-- Cartes de sélection -->
        <div class="cards-container">
            <!-- Carte Patient -->
            <div class="card">
                <div class="icon-wrapper">
                    <i class="fas fa-user icon"></i>
                </div>
                <h3>Patient</h3>
                <p>Gérez votre santé et vos rendez-vous médicaux en toute simplicité</p>
                
                <ul class="features">
                    <li class="feature">Prenez rendez-vous en ligne 24h/24</li>
                    <li class="feature">Accédez à votre dossier médical numérique</li>
                    <li class="feature">Recevez vos ordonnances électroniquement</li>
                    <li class="feature">Suivez votre traitement en temps réel</li>
                </ul>
                
                <a href="sign-up-patient.php" class="btn">
                    <i class="fas fa-user-plus"></i>
                    Créer un compte patient
                </a>
            </div>
            
            <!-- Carte Médecin -->
            <div class="card">
                <div class="icon-wrapper">
                    <i class="fas fa-user-md icon"></i>
                </div>
                <h3>Médecin</h3>
                <p>Optimisez votre pratique médicale avec nos outils professionnels</p>
                
                <ul class="features">
                    <li class="feature">Gérez votre planning de consultations</li>
                    <li class="feature">Accédez aux dossiers patients sécurisés</li>
                    <li class="feature">Prescrivez des traitements en ligne</li>
                    <li class="feature">Collaborez avec d'autres professionnels</li>
                </ul>
                
                <a href="sign-up-medecin.php" class="btn">
                    <i class="fas fa-stethoscope"></i>
                    Créer un compte médecin
                </a>
            </div>
        </div>
        
        <!-- Pied de page -->
        <div class="footer">
            <div class="footer-links">
                <a href="../home/index.php">
                    <i class="fas fa-home"></i>
                    Retour à l'accueil
                </a>
                <a href="sign-in.php">
                    <i class="fas fa-sign-in-alt"></i>
                    Connexion existante
                </a>
                <a href="#">
                    <i class="fas fa-info-circle"></i>
                    À propos
                </a>
                <a href="#">
                    <i class="fas fa-shield-alt"></i>
                    Sécurité des données
                </a>
            </div>
            
            <p class="login-prompt">
                Vous avez déjà un compte ? 
                <a href="sign-in.php">Connectez-vous ici</a>
            </p>
        </div>
    </div>

    <script src="../../assets/js/choice-script.js"></script>
</body>
</html>