<?php

require_once __DIR__ . '/../../../controllers/ServiceController.php';

$serviceController = new ServiceController();


$services = $serviceController->obtenirTousLesServices();


?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/projet_unifie/views/frontoffice/page-accueil/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet"
        href="/projet_unifie/views/backoffice/dashboard_service/css/dashboard.css?v=<?php echo time(); ?>">
</head>

<body>
    <header class="header">
        <div class="logo-section">
            <img src="../../images/logo.jpeg" alt="Logo Medsense" style="height: 50px; width: auto;">
        </div>
        <nav class="nav-links">

            <a href="dashboard.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
                <span>Dashboard</span>
            </a>
            <a href="../../frontoffice/logout.php" class="nav-link logout-link">
                <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span>Déconnexion</span>
            </a>

        </nav>
    </header>

    <main class="main-content">
        <section class="hero-section">
            <h1 class="hero-title">Supprimer un service</h1>
            <p class="hero-description">
                Sélectionnez un service dans la liste ci-dessous pour le supprimer. Cette action est irréversible.
            </p>
        </section>

        <section class="table-section">
            <!-- Search Bar -->
            <div class="search-container" style="max-width: 400px; margin-bottom: 20px;">
                <div class="search-box-wrapper" style="background: white; border: 1px solid #e2e8f0;">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="serviceSearch" class="search-input" placeholder="Rechercher un service..."
                        onkeyup="filterServices()">
                    <a href="#" class="search-clear" onclick="clearSearch()" style="display: none;" id="clearSearchBtn">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>

            <div class="table-container">
                <?php if (empty($services)): ?>
                    <p>Aucun service disponible à supprimer.</p>
                <?php else: ?>
                    <table class="services-edit-table" id="servicesTable">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Icon</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($service['image'])): ?>
                                            <img src="../../<?php echo htmlspecialchars($service['image']); ?>" alt="Image"
                                                class="service-thumbnail">
                                        <?php else: ?>
                                            <span class="no-image"><i class="fas fa-image"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($service['name']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($service['description'], 0, 50)) . '...'; ?></td>
                                    <td><i class="<?php echo htmlspecialchars($service['icon']); ?>"></i></td>
                                    <td>
                                        <a href="confirm_delete.php?id=<?php echo htmlspecialchars($service['id']); ?>"
                                            class="btn-delete-modern">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div id="noResults" style="display: none; text-align: center; padding: 20px; color: #64748b;">
                        Aucun service trouvé.
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
        function filterServices() {
            const input = document.getElementById('serviceSearch');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('servicesTable');
            if (!table) return;
            const tr = table.getElementsByTagName('tr');
            const clearBtn = document.getElementById('clearSearchBtn');
            const noResults = document.getElementById('noResults');
            let visibleCount = 0;

            if (filter.length > 0) {
                clearBtn.style.display = 'flex';
            } else {
                clearBtn.style.display = 'none';
            }

            for (let i = 1; i < tr.length; i++) {
                const tdName = tr[i].getElementsByTagName('td')[1]; // Name column (index 1 because Image is 0)
                const tdDesc = tr[i].getElementsByTagName('td')[2]; // Desc column

                if (tdName || tdDesc) {
                    const txtValueName = tdName.textContent || tdName.innerText;
                    const txtValueDesc = tdDesc.textContent || tdDesc.innerText;

                    if (txtValueName.toLowerCase().indexOf(filter) > -1 || txtValueDesc.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        visibleCount++;
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }

            if (visibleCount === 0 && filter.length > 0) {
                if (noResults) noResults.style.display = 'block';
            } else {
                if (noResults) noResults.style.display = 'none';
            }
        }

        function clearSearch() {
            const input = document.getElementById('serviceSearch');
            input.value = '';
            filterServices();
            input.focus();
        }
    </script>

</body>

</html>