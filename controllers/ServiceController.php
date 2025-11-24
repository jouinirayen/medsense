<?php
/**
 * Service Controller
 * Handles all form submissions, redirects, and database queries
 */

require_once __DIR__ . '/../config/config.php';



class ServiceController {
    



    

    

    

    
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
     * Add a new service using ServiceModel
     */
    public function addService(ServiceModel $service) {
        try {
            $pdo = (new config())->getConnexion();
            $stmt = $pdo->prepare("INSERT INTO services (name, description, icon, link, image) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([
                $service->getName(),
                $service->getDescription(),
                $service->getIcon(),
                $service->getLink(),
                $service->getImage()
            ]);
        } catch (PDOException $e) {
            die("Erreur lors de l'ajout du service: " . $e->getMessage());
        }
    }
    
    /**
     * Update a service
     */
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
     * Update a service using ServiceModel
     */
    public function updateService($id, ServiceModel $service) {
        try {
            $pdo = (new config())->getConnexion();
            $stmt = $pdo->prepare("UPDATE services SET name = ?, description = ?, icon = ?, link = ?, image = ? WHERE id = ?");
            return $stmt->execute([
                $service->getName(),
                $service->getDescription(),
                $service->getIcon(),
                $service->getLink(),
                $service->getImage(),
                $id
            ]);
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
