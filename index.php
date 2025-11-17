<?php

require_once 'config/config.php';
require_once 'config/db.php';
require_once 'config/auth.php';

$pageTitle = "Accueil";

include 'config/header.php';
?>

<div style="text-align: center; padding: 3rem 0;">
    <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem;">Bienvenue dans le Système de Réclamations et Urgences</h2>
    
    <?php if (isLoggedIn()): ?>
        <p style="font-size: 1.1rem; margin-bottom: 2rem;">
            Connecté en tant que <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
        </p>

        <?php if (isAdmin()): ?>
            <p style="margin-bottom: 2rem;">
                <a href="app/Views/back/admin_reclamations.php" class="btn btn-success" style="padding: 1rem 2rem; font-size: 1.1rem;">
                    Aller au Tableau de Bord Admin
                </a>
            </p>
        <?php else: ?>
            <div class="card" style="max-width: 600px; margin: 2rem auto;">
                <h3>Menu Utilisateur</h3>
                <div class="card-actions" style="flex-direction: column;">
                    <a href="app/Views/front/mes_reclamations.php" class="btn" style="width: 100%; text-align: center;">📋 Mes Réclamations</a>
                    <a href="app/Views/front/creer_reclamation.php" class="btn btn-success" style="width: 100%; text-align: center;">✏️ Créer une Réclamation</a>
                    <a href="app/Views/front/urgence.php" class="btn btn-danger" style="width: 100%; text-align: center; animation: pulse 1s infinite;">🚨 Alerte Urgence</a>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p style="font-size: 1.1rem; margin-bottom: 2rem;">
            Vous n'êtes pas connecté. Veuillez vous connecter ou créer un compte.
        </p>
        <div class="card" style="max-width: 600px; margin: 2rem auto;">
            <div class="card-actions" style="flex-direction: column;">
                <a href="login.php" class="btn btn-success" style="width: 100%; text-align: center;">🔐 Se Connecter</a>
                <a href="register.php" class="btn" style="width: 100%; text-align: center;">📝 S'Inscrire</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
include 'config/footer.php';
?>
