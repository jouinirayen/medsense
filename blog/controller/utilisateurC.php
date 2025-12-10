<?php
// Controller/utilisateurC.php
require_once '../../config.php';

class utilisateurC {

    public function inscrire($utilisateur) {
        $sql = "INSERT INTO utilisateur (nom, prenom, email, motdepasse) 
                VALUES (:nom, :prenom, :email, :motdepasse)";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':nom', $utilisateur->getNom());
            $req->bindValue(':prenom', $utilisateur->getPrenom());
            $req->bindValue(':email', $utilisateur->getEmail());
            $req->bindValue(':motdepasse', $utilisateur->getMotdepasse());
            $req->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function connecter($email) {
        $sql = "SELECT * FROM utilisateur WHERE email = :email LIMIT 1";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':email', $email);
        $req->execute();
        return $req->fetch();
    }

    public function getUtilisateurById($id) {
        $sql = "SELECT * FROM utilisateur WHERE id = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        $req->execute();
        return $req->fetch();
    }
}
?>