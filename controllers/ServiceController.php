<?php
/**
 * Service Controller
 * Handles all form submissions, redirects, and database queries
 */

include __DIR__ . '/../config/config.php';

class ServiceController {
    
    /**
     * Handle add service form submission (kept for backward compatibility)
     */
    public function gererAjout() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = '';
            if (isset($_POST['name'])) {
                $name = $_POST['name'];
            }

            $description = '';
            if (isset($_POST['description'])) {
                $description = $_POST['description'];
            }

            $icon = '';
            if (isset($_POST['icon'])) {
                $icon = $_POST['icon'];
            }

            $link = '';
            if (isset($_POST['link'])) {
                $link = $_POST['link'];
            }

            $image = '';
            if (isset($_POST['image'])) {
                $image = $_POST['image'];
            }
            
            if (!empty($name) && !empty($description) && !empty($icon) && !empty($link) && !empty($image)) {
                $this->ajouterService($name, $description, $icon, $link, $image);
                header('Location: ../views/dashboard.php?message=Service ajouté avec succès!');
            } else {
                header('Location: ../views/dashboard.php?error=Tous les champs sont requis!');
            }
            return;
        }
    }
    
    /**
     * Handle edit service form submission (kept for backward compatibility)
     */
    public function gererModification() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = '';
            if (isset($_POST['id'])) {
                $id = $_POST['id'];
            }

            $name = '';
            if (isset($_POST['name'])) {
                $name = $_POST['name'];
            }

            $description = '';
            if (isset($_POST['description'])) {
                $description = $_POST['description'];
            }

            $icon = '';
            if (isset($_POST['icon'])) {
                $icon = $_POST['icon'];
            }

            $link = '';
            if (isset($_POST['link'])) {
                $link = $_POST['link'];
            }

            $image = '';
            if (isset($_POST['image'])) {
                $image = $_POST['image'];
            }
            
            if (!empty($id) && !empty($name) && !empty($description) && !empty($icon) && !empty($link) && !empty($image)) {
                $this->modifierService($id, $name, $description, $icon, $link, $image);
                header('Location: ../views/dashboard.php?message=Service modifié avec succès!');
            } else {
                header('Location: ../views/dashboard.php?error=Tous les champs sont requis!');
            }
            return;
        }
    }
    
    /**
     * Handle delete service (kept for backward compatibility)
     */
    public function gererSuppression() {
        if (isset($_GET['delete_id'])) {
            $id = $_GET['delete_id'];
            $this->supprimerService($id);
            header('Location: ../views/dashboard.php?message=Service supprimé avec succès!');
            return;
        }
    }
    
    // ============================================
    // Méthodes de requêtes SQL (Data Access)
    // ============================================
    
    /**
     * Get all services from the database
     */
    public function obtenirTousLesServices() {
        try {
            $pdo = (new config())->getConnexion();
            $stmt = $pdo->query("SELECT * FROM services ORDER BY id DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die("Erreur lors de la récupération des services: " . $e->getMessage());
        }
    }
    
    /**
     * Get a service by ID
     */
    public function obtenirServiceParId($id) {
        try {
            $pdo = (new config())->getConnexion();
            $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            die("Erreur lors de la récupération du service: " . $e->getMessage());
        }
    }
    
    /**
     * Add a new service
     */
    public function ajouterService($name, $description, $icon, $link, $image = '') {
        try {
            $pdo = (new config())->getConnexion();
            $stmt = $pdo->prepare("INSERT INTO services (name, description, icon, link, image) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$name, $description, $icon, $link, $image]);
        } catch (PDOException $e) {
            die("Erreur lors de l'ajout du service: " . $e->getMessage());
        }
    }
    
    /**
     * Update a service
     */
    public function modifierService($id, $name, $description, $icon, $link, $image = '') {
        try {
            $pdo = (new config())->getConnexion();
            $stmt = $pdo->prepare("UPDATE services SET name = ?, description = ?, icon = ?, link = ?, image = ? WHERE id = ?");
            return $stmt->execute([$name, $description, $icon, $link, $image, $id]);
        } catch (PDOException $e) {
            die("Erreur lors de la mise à jour du service: " . $e->getMessage());
        }
    }
    
    /**
     * Delete a service
     */
    public function supprimerService($id) {
        try {
            $pdo = (new config())->getConnexion();
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            die("Erreur lors de la suppression du service: " . $e->getMessage());
        }
    }
    
    /**
     * Search services by name
     */
    public function rechercherServices($searchTerm) {
        try {
            $pdo = (new config())->getConnexion();
            
            if (empty($searchTerm)) {
                return $this->obtenirTousLesServices();
            }
            
            // Recherche exacte sur le nom (sans utiliser de caractère special LIKE)
            $stmt = $pdo->prepare("SELECT * FROM services WHERE name = ? ORDER BY id DESC");
            $stmt->execute([$searchTerm]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die("Erreur lors de la recherche de services: " . $e->getMessage());
        }
    }
    
}
?>
