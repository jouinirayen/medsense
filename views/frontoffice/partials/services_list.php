<section class="services-section">
    <div class="section-header">
        <div class="section-title-wrapper">
            <h2 class="section-title">Nos Services</h2>
            <p class="section-subtitle">Découvrez notre gamme complète de services médicaux</p>
        </div>

        <?php if (!empty($services)): ?>
            <div class="services-count-badge">
                <i class="fas fa-list"></i>
                <span><?php echo $totalServicesCount; ?> service(s)</span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Conteneur des services (avec classe pour le scroll auto si nécessaire) -->
    <div class="slider-wrapper <?php echo $isAutoScroll ? 'auto-scroll-container' : ''; ?>">
        <div class="services-grid <?php echo $isAutoScroll ? 'auto-scroll' : ''; ?>">

            <?php if (empty($servicesToDisplay)): ?>
                <div class="no-services">
                    <p>Aucun service trouvé.</p>
                </div>
            <?php else: ?>

                <?php foreach ($servicesToDisplay as $service): ?>
                    <?php
                    $link = generateBookingLink($service);

                    // Gestion de l'image de fond
                    $bgStyle = '';
                    if (!empty($service['image'])) {
                        $bgStyle = "background-image: url('../" . $service['image'] . "'); background-size: cover; background-position: center; background-repeat: no-repeat;";
                    }
                    ?>

                    <div class="service-card" style="<?php echo $bgStyle; ?>">
                        <div class="service-icon">
                            <i class="<?php echo $service['icon']; ?>"></i>
                        </div>

                        <h3 class="service-title"><?php echo $service['name']; ?></h3>
                        <p class="service-description"><?php echo $service['description']; ?></p>

                        <div class="service-footer">
                            <?php if ($link): ?>
                                <a href="<?php echo $link; ?>" class="view-doctors-link">Voir les détails</a>
                                <a href="<?php echo $link; ?>" class="arrow-button">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </div>
</section>