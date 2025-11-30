<?php
require_once 'config/config.php';

$pageTitle = "Accueil";

include 'config/header.php';
?>

<div style="text-align: center; padding: 3rem 0;">
    <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem;">
        Bienvenue dans le Système de Réclamations et Urgences
    </h2>

    <div class="card" style="max-width: 600px; margin: 2rem auto;">
        <h3>Menu</h3>
        <div class="card-actions" style="flex-direction: column;">
            <a href="views/frontoffice/reclamation/mes_reclamations.php" class="btn" style="width: 100%;">📋 Mes Réclamations</a>
            <a href="views/frontoffice/reclamation/creer_reclamation.php" class="btn btn-success" style="width: 100%;">✏️ Créer une Réclamation</a>
            <a href="views/frontoffice/reclamation/urgence.php" class="btn btn-danger" style="width: 100%; animation: pulse 1s infinite;">🚨 Alerte Urgence</a>
              <a href="views/backoffice/reponse/admin_reclamations.php" class="btn btn-success" style="padding: 1rem 2rem; font-size: 1.1rem;">
               
        </div>
    </div>
</div>

<?php include 'config/footer.php'; ?>
