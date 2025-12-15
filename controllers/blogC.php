
<?php
require_once '../../config/config.php';
require_once '../../models/blog.php';

class blogC {

    // Connexion à la base de données
    private function getDbConnexion() {
        return config::getConnexion();
    }

    public function ajouterPost($publication) {
    $db = $this->getDbConnexion();
    $sql = "INSERT INTO blog (contenu, imageUrl, createdAt, utilisateur_id, statut, image_statut) 
             VALUES (:contenu, :imageUrl, :createdAt, :utilisateur_id, 'en_attente', 'en_attente')";
    
    $req = $db->prepare($sql);
    $req->execute([
        ':contenu'        => $publication->getContenu(),
        ':imageUrl'       => $publication->getImageUrl(),
        ':createdAt'      => $publication->getCreatedAt(),
        ':utilisateur_id' => $publication->getUtilisateurId()
    ]);
}

    // AFFICHER LES POSTS PUBLICS (approuvés) + infos utilisateur
    public function publier(){
        $sql = "SELECT b.*, u.prenom, u.nom 
                FROM blog b 
                LEFT JOIN utilisateur u ON b.utilisateur_id = u.id 
                WHERE b.statut = 'approuve' 
                ORDER BY b.createdAt DESC";
        $db = $this->getDbConnexion();
        try{
            $liste = $db->query($sql);
            return $liste->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            die('Erreur: '.$e->getMessage());
        }
    }

    // ADMIN : voir tous les posts (même en attente, refusé, etc.)
    public function listeTousPostsAdmin() {
        $sql = "SELECT 
                    b.id, 
                    b.contenu, 
                    b.imageUrl,
                    b.createdAt, 
                    b.utilisateur_id, 
                    b.statut, 
                    b.image_statut,
                    u.prenom, 
                    u.nom, 
                    u.email 
                FROM blog b 
                LEFT JOIN utilisateur u ON b.utilisateur_id = u.id 
                ORDER BY b.createdAt DESC";

        $db = $this->getDbConnexion();
        try {
            $liste = $db->query($sql);
            return $liste->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Approuver un post
    public function approuverPost($id) {
        $sql = "UPDATE blog SET statut = 'approuve' WHERE id = :id";
        $db = $this->getDbConnexion();
        try {
            $req = $db->prepare($sql);
            $req->execute([':id' => $id]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    // Refuser un post
    public function refuserPost($id) {
        $sql = "UPDATE blog SET statut = 'refuse' WHERE id = :id";
        $db = $this->getDbConnexion();
        try {
            $req = $db->prepare($sql);
            $req->execute([':id' => $id]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    // SUPPRIMER UN POST
    public function deletePost($id) {
        $db = $this->getDbConnexion();
        try {
            $req = $db->prepare('DELETE FROM blog WHERE id = :id');
            $req->execute([':id' => $id]);
        } catch(Exception $e) {
            die('ERROR : ' . $e->getMessage());
        }
    }

    // MODIFIER UN POST
    public function updatePost($publication, $id) {
        $db = $this->getDbConnexion();
        try {
            $req = $db->prepare('UPDATE blog 
                                 SET contenu = :c, imageUrl = :im 
                                 WHERE id = :id');

            $req->execute([
                ':id' => $id,
                ':c'  => $publication->getContenu(),
                ':im' => $publication->getImageUrl()
            ]);
        } catch(Exception $e) {
            die('ERROR : ' . $e->getMessage());
        }
    }

    // Compteur des posts en attente
    public function compterEnAttente() {
        $sql = "SELECT COUNT(*) FROM blog WHERE statut = 'en_attente'";
        $db = $this->getDbConnexion();
        try {
            return (int)$db->query($sql)->fetchColumn();
        } catch(Exception $e) {
            return 0;
        }
    }

    // Récupérer un post par ID
    public function getPostById($id) {
        $sql = "SELECT * FROM blog WHERE id = :id";
        $db = $this->getDbConnexion();
        try {
            $req = $db->prepare($sql);
            $req->execute([':id' => $id]);
            return $req->fetch(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            return false;
        }
    }
    public function getPostsByUserId($user_id) {
    $sql = "SELECT b.*, u.prenom, u.nom 
            FROM blog b 
            LEFT JOIN utilisateur u ON b.utilisateur_id = u.id 
            WHERE b.utilisateur_id = :user_id AND b.statut = 'approuve'
            ORDER BY b.createdAt DESC";
    $db = config::getConnexion();
    $req = $db->prepare($sql);
    $req->execute([':user_id' => $user_id]);
    return $req->fetchAll(PDO::FETCH_ASSOC);
}
}
?>
