<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisissez votre profil - Medcare</title>
    <link rel="stylesheet" href="../../assets/vendors/fontawesome/css/all.min.css">

</head>
<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #e8f4ff 0%, #c2e0ff 100%);
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
    color: #333;
}


.water-bubble {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(5px);
    z-index: 1;
}

.bubble-1 {
    width: 120px;
    height: 120px;
    top: 10%;
    left: 5%;
    animation: float 20s infinite ease-in-out;
}

.bubble-2 {
    width: 80px;
    height: 80px;
    top: 60%;
    right: 10%;
    animation: float 15s infinite ease-in-out reverse;
    animation-delay: 2s;
}

.bubble-3 {
    width: 60px;
    height: 60px;
    bottom: 15%;
    left: 15%;
    animation: float 25s infinite ease-in-out;
    animation-delay: 1s;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0) translateX(0) rotate(0deg);
    }
    25% {
        transform: translateY(-20px) translateX(10px) rotate(90deg);
    }
    50% {
        transform: translateY(10px) translateX(-10px) rotate(180deg);
    }
    75% {
        transform: translateY(-10px) translateX(15px) rotate(270deg);
    }
}


.wave {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 150px;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="rgba(255,255,255,0.1)"/></svg>');
    background-size: 1200px 100%;
    opacity: 0.6;
}

.wave:nth-child(1) {
    animation: wave 25s linear infinite;
}

.wave:nth-child(2) {
    animation: wave 20s linear infinite reverse;
    opacity: 0.4;
}

@keyframes wave {
    0% {
        background-position-x: 0;
    }
    100% {
        background-position-x: 1200px;
    }
}


.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
    position: relative;
    z-index: 2;
}

.header {
    text-align: center;
    margin-bottom: 60px;
    animation: fadeInDown 0.8s ease-out;
}

.logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin-bottom: 20px;
}

.logo-icon {
    font-size: 3.5rem;
    color: #2a74ff;
    filter: drop-shadow(0 4px 8px rgba(42, 116, 255, 0.3));
    animation: pulse 2s infinite ease-in-out;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.05);
        opacity: 0.8;
    }
}

.logo span {
    font-size: 3rem;
    font-weight: 800;
    background: linear-gradient(135deg, #2a74ff 0%, #1d5fe0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 4px 12px rgba(42, 116, 255, 0.2);
}

.subtitle {
    font-size: 1.3rem;
    color: #6c7a96;
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.6;
}


.cards-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 40px;
    margin-bottom: 60px;
}

.card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(42, 116, 255, 0.15);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
    overflow: hidden;
}

.card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #2a74ff 0%, #1d5fe0 100%);
}

.card:hover {
    transform: translateY(-15px) scale(1.02);
    box-shadow: 0 30px 80px rgba(42, 116, 255, 0.25);
}

.card:nth-child(1) {
    animation: slideInLeft 0.8s ease-out;
}

.card:nth-child(2) {
    animation: slideInRight 0.8s ease-out;
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.icon-wrapper {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e8f4ff 0%, #c2e0ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    position: relative;
    transition: all 0.3s ease;
}

.card:hover .icon-wrapper {
    transform: scale(1.1) rotate(5deg);
    background: linear-gradient(135deg, #2a74ff 0%, #1d5fe0 100%);
}

.icon {
    font-size: 40px;
    color: #2a74ff;
    transition: all 0.3s ease;
}

.card:hover .icon {
    color: white;
    transform: scale(1.1);
}

.card h3 {
    font-size: 2.2rem;
    color: #0c1b45;
    text-align: center;
    margin-bottom: 15px;
    font-weight: 700;
}

.card p {
    color: #6c7a96;
    text-align: center;
    margin-bottom: 30px;
    font-size: 1.1rem;
    line-height: 1.6;
}


.features {
    list-style: none;
    margin: 30px 0 40px;
}

.feature {
    padding: 12px 20px;
    margin-bottom: 10px;
    background: rgba(42, 116, 255, 0.05);
    border-radius: 12px;
    border-left: 4px solid #2a74ff;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
}

.feature:before {
    content: '✓';
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    background: #2a74ff;
    color: white;
    border-radius: 50%;
    margin-right: 12px;
    font-size: 14px;
    font-weight: bold;
    flex-shrink: 0;
}

.feature:hover {
    background: rgba(42, 116, 255, 0.1);
    transform: translateX(5px);
}


.btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    width: 100%;
    padding: 18px 30px;
    background: linear-gradient(135deg, #2a74ff 0%, #1d5fe0 100%);
    color: white;
    text-decoration: none;
    border-radius: 16px;
    font-size: 1.1rem;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    border: none;
    cursor: pointer;
}

.btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn:hover::before {
    width: 300px;
    height: 300px;
}

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(42, 116, 255, 0.3);
}

.btn:active {
    transform: translateY(-1px);
}

.btn i {
    font-size: 1.2rem;
    transition: transform 0.3s ease;
}

.btn:hover i {
    transform: translateX(5px);
}


.footer {
    text-align: center;
    margin-top: 40px;
    padding-top: 40px;
    border-top: 1px solid rgba(42, 116, 255, 0.1);
    animation: fadeInUp 0.8s ease-out;
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

.footer-links {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 25px;
    margin-bottom: 30px;
}

.footer-links a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #2a74ff;
    text-decoration: none;
    font-weight: 500;
    padding: 10px 20px;
    border-radius: 12px;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.8);
}

.footer-links a:hover {
    background: #2a74ff;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(42, 116, 255, 0.2);
}

.footer-links a i {
    font-size: 16px;
}

.login-prompt {
    color: #6c7a96;
    font-size: 1.1rem;
    margin-top: 20px;
}

.login-prompt a {
    color: #2a74ff;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
}

.login-prompt a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: #2a74ff;
    transition: width 0.3s ease;
}

.login-prompt a:hover::after {
    width: 100%;
}


@media (max-width: 768px) {
    .container {
        padding: 20px 15px;
    }
    
    .logo {
        flex-direction: column;
        gap: 10px;
    }
    
    .logo-icon {
        font-size: 2.5rem;
    }
    
    .logo span {
        font-size: 2.2rem;
    }
    
    .subtitle {
        font-size: 1.1rem;
        padding: 0 10px;
    }
    
    .cards-container {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .card {
        padding: 30px 25px;
    }
    
    .card h3 {
        font-size: 1.8rem;
    }
    
    .footer-links {
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }
    
    .footer-links a {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
    
    .wave {
        height: 100px;
    }
}

@media (max-width: 480px) {
    .card {
        padding: 25px 20px;
    }
    
    .icon-wrapper {
        width: 70px;
        height: 70px;
    }
    
    .icon {
        font-size: 30px;
    }
    
    .card h3 {
        font-size: 1.6rem;
    }
    
    .btn {
        padding: 16px 20px;
        font-size: 1rem;
    }
    
    .feature {
        padding: 10px 15px;
        font-size: 0.95rem;
    }
    
    .feature:before {
        width: 20px;
        height: 20px;
        font-size: 12px;
    }
    
    .bubble-1 {
        width: 80px;
        height: 80px;
    }
    
    .bubble-2, .bubble-3 {
        width: 50px;
        height: 50px;
    }
}


::-webkit-scrollbar {
    width: 10px;
}

::-webkit-scrollbar-track {
    background: rgba(42, 116, 255, 0.05);
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #2a74ff 0%, #1d5fe0 100%);
    border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #1d5fe0 0%, #2a74ff 100%);
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}


@media print {
    .water-bubble,
    .wave,
    .btn,
    .footer-links {
        display: none;
    }
    
    .card {
        box-shadow: none;
        border: 2px solid #333;
    }
}
</style>
<body>
    
    <div class="water-bubble bubble-1"></div>
    <div class="water-bubble bubble-2"></div>
    <div class="water-bubble bubble-3"></div>
    
    
    <div class="wave"></div>
    <div class="wave"></div>
    
    <div class="container">
   
        <div class="header">
            <div class="logo">
                <i class="fas fa-tint logo-icon"></i>
                <span>Medsense</span>
            </div>
            <p class="subtitle">Choisissez votre profil pour accéder à nos services de santé en ligne</p>
        </div>
        
        
        <div class="cards-container">
         
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