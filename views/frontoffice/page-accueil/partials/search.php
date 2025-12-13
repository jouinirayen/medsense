<section class="search-section">
    <div class="search-container">
        <form method="GET" action="front.php" class="search-form" id="aiSearchForm">
            <div class="search-box-wrapper">
                <i class="fas fa-magic search-icon" style="color: #6366f1;"></i> <!-- Icone Magique -->
                <input type="text" name="search" id="searchInput" class="search-input"
                    placeholder="Décrivez votre problème (ex: mal au dos)..." value="<?php echo $searchTerm; ?>"
                    autocomplete="off">
                <!-- Bouton pour effacer la recherche -->
                <?php if (!empty($searchTerm)): ?>
                    <a href="front.php" class="search-clear" title="Effacer la recherche">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-button" id="searchBtn">
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

<script>
    document.getElementById('aiSearchForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const query = document.getElementById('searchInput').value.trim();
        if (!query) return;

        const btn = document.getElementById('searchBtn');
        const icon = btn.querySelector('i');
        const originalText = btn.querySelector('span').innerText;

        // UI Feedback (Loading)
        btn.disabled = true;
        icon.className = 'fas fa-brain fa-spin'; // Brain spinning
        btn.querySelector('span').innerText = 'Analyse IA...';

        // Call Backend
        fetch('search_ai_endpoint.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query: query })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.match) {
                    // Success: Redirect to search page with MATCHED term
                    window.location.href = 'front.php?search=' + encodeURIComponent(data.match);
                } else {
                    // Fallback: Submit normally if no AI match
                    document.getElementById('aiSearchForm').submit();
                }
            })
            .catch(err => {
                // Fallback on error
                console.error(err);
                document.getElementById('aiSearchForm').submit();
            });
    });
</script>