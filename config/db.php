<?php
/**
 * Classe pour gérer la connexion à la base de données
 */

class Database {
    private $connection;
    private static $instance = null;

    private function __construct() {
        $this->connect();
    }

    /**
     * Singleton - obtenir l'instance unique
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Connexion à la base de données
     */
    private function connect() {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->connection->connect_error) {
                throw new Exception("Erreur de connexion: " . $this->connection->connect_error);
            }

            // Définir le charset
            $this->connection->set_charset("utf8mb4");
        } catch (Exception $e) {
            die("Erreur de base de données: " . $e->getMessage());
        }
    }

    /**
     * Obtenir la connexion
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Exécuter une requête préparée
     */
    public function execute($sql, $params = array(), $types = '') {
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Erreur de préparation: " . $this->connection->error);
        }

        if (!empty($params)) {
            // Si types n'est pas spécifié, on le génère automatiquement
            if (empty($types)) {
                $types = str_repeat('s', count($params));
            }
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new Exception("Erreur d'exécution: " . $stmt->error);
        }

        return $stmt;
    }

    /**
     * Récupérer les résultats d'une requête
     */
    public function fetchAll($sql, $params = array(), $types = '') {
        $stmt = $this->execute($sql, $params, $types);
        $result = $stmt->get_result();
        $data = array();

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt->close();
        return $data;
    }

    /**
     * Récupérer une seule ligne
     */
    public function fetch($sql, $params = array(), $types = '') {
        $stmt = $this->execute($sql, $params, $types);
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }

    /**
     * Récupérer l'ID de la dernière insertion
     */
    public function lastInsertId() {
        return $this->connection->insert_id;
    }

    /**
     * Obtenir le nombre de lignes affectées
     */
    public function affectedRows() {
        return $this->connection->affected_rows;
    }

    /**
     * Fermer la connexion
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

// Créer une instance de la base de données
$db = Database::getInstance();

?>
