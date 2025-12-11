<?php
require_once '../../config.php';

class commentaireC {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO('mysql:host=localhost;dbname=blog 2', 'root', '');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->exec("SET NAMES utf8");
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }
    // AJOUTER UN COMMENTAIRE → il passe automatiquement en "en_attente"
    public function ajouterCommentaire($commentaire) {
        $sql = "INSERT INTO commentaires (blog_id, contenu, utilisateur_id, statut, created_at) 
                VALUES (:blog_id, :contenu, :utilisateur_id, 'approuve', NOW())";

        try {
            $db = config::getConnexion();
            $query = $db->prepare($sql);
            $query->execute([
                'blog_id'       => $commentaire->getBlogId(),
                'contenu'       => $commentaire->getContenu(),
                'utilisateur_id' => $commentaire->getUtilisateurId()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            error_log('Erreur ajout commentaire : ' . $e->getMessage());
            return false;
        }
    }

    // LISTE DES COMMENTAIRES → avec prénom + nom + approuvés seulement
    public function listeCommentaires($blog_id) {
        $sql = "SELECT 
                    c.id,
                    c.contenu,
                    c.created_at,
                    c.utilisateur_id,
                    COALESCE(u.prenom, 'Anonyme') AS prenom,
                    COALESCE(u.nom, '') AS nom
                FROM commentaires c
                LEFT JOIN utilisateur u ON c.utilisateur_id = u.id
                WHERE c.blog_id = :blog_id 
                  AND c.statut = 'approuve'
                ORDER BY c.created_at ASC";

        try {
            $db = config::getConnexion();
            $req = $db->prepare($sql);
            $req->bindValue(':blog_id', $blog_id, PDO::PARAM_INT);
            $req->execute();
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Erreur liste commentaires : ' . $e->getMessage());
            return [];
        }
    }

    // LISTE TOUS LES COMMENTAIRES (pour l'admin)
    public function listeTousCommentairesAdmin() {
        $sql = "SELECT c.*, b.contenu as post_contenu, b.id as post_id
                FROM commentaires c
                LEFT JOIN blog b ON c.blog_id = b.id
                ORDER BY c.created_at DESC";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste->fetchAll();
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
        }
    }

    // APPROUVER UN COMMENTAIRE
    public function approuverCommentaire($id) {
        $sql = "UPDATE commentaires SET statut = 'approuve' WHERE id = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
        }
    }

    // REFUSER UN COMMENTAIRE
    public function refuserCommentaire($id) {
        $sql = "UPDATE commentaires SET statut = 'refuse' WHERE id = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
        }
    }

    // SUPPRIMER UN COMMENTAIRE
    public function supprimerCommentaire($id) {
        $sql = "DELETE FROM commentaires WHERE id = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            echo 'Erreur: '. $e->getMessage();
        }
    }

    // RÉCUPÉRER UN COMMENTAIRE PAR ID
    public function getCommentaireById($id) {
        $sql = "SELECT * FROM commentaires WHERE id = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            echo 'Erreur: '. $e->getMessage();
        }
    }

    // MODIFIER UN COMMENTAIRE
    public function modifierCommentaire($id, $nouveauContenu) {
        $sql = "UPDATE commentaires SET contenu = :contenu, created_at = NOW() WHERE id = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'contenu' => $nouveauContenu,
                'id' => $id
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '. $e->getMessage();
        }
    }
    public function compterEnAttente() {
    $sql = "SELECT COUNT(*) FROM commentaires WHERE statut = 'en_attente'";
    $db = config::getConnexion();
    return (int)$db->query($sql)->fetchColumn();
}
public function listeTousCommentairesDuPost($post_id) {
    $sql = "SELECT c.*, u.prenom, u.nom 
            FROM commentaires c 
            LEFT JOIN utilisateur u ON c.utilisateur_id = u.id 
            WHERE c.blog_id = :id 
            ORDER BY c.created_at DESC";
    
    $db = config::getConnexion();
    try {
        $req = $db->prepare($sql);
        $req->execute([':id' => $post_id]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // En cas d'erreur (table inexistante, etc.), on renvoie un tableau vide pour éviter le crash
        return [];
    }
}
}
?>