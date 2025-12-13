<?php
/**
 * Service Controller
 * Handles all form submissions, redirects, and database queries
 */

require_once __DIR__ . '/../config/config.php';


class ServiceController
{
    // ============================================
    // Méthodes de requêtes SQL (Data Access)
    // ============================================

    /**
     * Get all services from the database
     * @return array
     */
    public function obtenirTousLesServices()
    {
        try {
            $pdo = (new config())->getConnexion();
            $stmt = $pdo->query("SELECT * FROM services ORDER BY id DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur lors de la récupération des services: " . $e->getMessage());
        }
    }

    /**
     * Get a service by ID
     * @return array|false
     */
    public function obtenirServiceParId($id)
    {
        try {
            $pdo = (new config())->getConnexion();
            $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur lors de la récupération du service: " . $e->getMessage());
        }
    }

    /**
     * Add a new service
     * @param array $service
     */
    public function addService($service)
    {
        try {
            $pdo = (new config())->getConnexion();
            $stmt = $pdo->prepare("INSERT INTO services (name, description, icon, link, image) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([
                $service['name'],
                $service['description'],
                $service['icon'],
                $service['link'] ?? '',
                $service['image']
            ]);
        } catch (PDOException $e) {
            die("Erreur lors de l'ajout du service: " . $e->getMessage());
        }
    }

    /**
     * Update a service
     * @param int $id
     * @param array $service
     */
    public function updateService($id, $service)
    {
        try {
            $pdo = (new config())->getConnexion();
            $stmt = $pdo->prepare("UPDATE services SET name = ?, description = ?, icon = ?, link = ?, image = ? WHERE id = ?");
            return $stmt->execute([
                $service['name'],
                $service['description'],
                $service['icon'],
                $service['link'] ?? '',
                $service['image'],
                $id
            ]);
        } catch (PDOException $e) {
            die("Erreur lors de la mise à jour du service: " . $e->getMessage());
        }
    }

    /**
     * Delete a service
     */
    public function supprimerService($id)
    {
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
     * @return array
     */
    public function rechercherServices($searchTerm)
    {
        try {
            $pdo = (new config())->getConnexion();

            if (empty($searchTerm)) {
                return $this->obtenirTousLesServices();
            }

            // Recherche exacte sur le nom
            $stmt = $pdo->prepare("SELECT * FROM services WHERE name = ? ORDER BY id DESC");
            $stmt->execute([$searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur lors de la recherche de services: " . $e->getMessage());
        }
    }
}
?>