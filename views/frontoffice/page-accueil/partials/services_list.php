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

    <!-- Conteneur externe pour positionner les flèches de façon stable -->
    <div class="slider-outer-container" style="position: relative;">

        <!-- Navigation Arrows (Siblings of the scroll wrapper) -->
        <button id="scrollLeft" class="scroll-arrow left-arrow"><i class="fas fa-chevron-left"></i></button>
        <button id="scrollRight" class="scroll-arrow right-arrow"><i class="fas fa-chevron-right"></i></button>

        <!-- Scrollable Wrapper -->
        <div class="slider-wrapper" id="servicesWrapper" style="overflow-x: auto; scroll-behavior: smooth;">
            <style>
                #servicesWrapper::-webkit-scrollbar {
                    display: none;
                }

                #servicesWrapper {
                    -ms-overflow-style: none;
                    scrollbar-width: none;
                }
            </style>

            <div class="services-grid <?php echo $isAutoScroll ? 'auto-scroll' : ''; ?>" id="servicesGrid">

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
                            $bgStyle = "background-image: url('../../" . $service['image'] . "'); background-size: cover; background-position: center; background-repeat: no-repeat;";
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
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wrapper = document.getElementById('servicesWrapper');
        const leftBtn = document.getElementById('scrollLeft');
        const rightBtn = document.getElementById('scrollRight');

        if (wrapper && leftBtn && rightBtn) {
            leftBtn.addEventListener('click', () => {
                wrapper.scrollBy({ left: -350, behavior: 'smooth' });
            });

            rightBtn.addEventListener('click', () => {
                wrapper.scrollBy({ left: 350, behavior: 'smooth' });
            });
        }
    });
</script>