<section class="search-section">
    <div class="search-container">
        <form method="GET" action="front.php" class="search-form">
            <div class="search-box-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" class="search-input" placeholder="Rechercher un service..."
                    value="<?php echo $searchTerm; ?>" autocomplete="off">
                <!-- Bouton pour effacer la recherche -->
                <?php if (!empty($searchTerm)): ?>
                    <a href="front.php" class="search-clear" title="Effacer la recherche">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-button">
                <i class="fas fa-search"></i>
                <span>Rechercher</span>
            </button>
        </form>

        <!-- Résultat de la recherche -->
        <?php if (!empty($searchTerm)): ?>
            <div style="text-align: center; margin-top: 20px;">
                <p class="search-results-count">
                    <i class="fas fa-check-circle" style="margin-right: 8px; color: #10b981;"></i>
                    <?php echo $totalServicesCount; ?> service(s) trouvé(s) pour
                    "<?php echo $searchTerm; ?>"
                </p>
            </div>
        <?php endif; ?>
    </div>
</section>