<?php
// Model/utilisateur.php
class utilisateur {
    private $id;
    private $nom;
    private $prenom;
    private $email;
    private $motdepasse;

    public function __construct($nom, $prenom, $email, $motdepasse) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->motdepasse = password_hash($motdepasse, PASSWORD_DEFAULT);
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getPrenom() { return $this->prenom; }
    public function getNomComplet() { return $this->prenom . ' ' . $this->nom; }
    public function getEmail() { return $this->email; }
    public function getMotdepasse() { return $this->motdepasse; }

    // Setters
    public function setId($id) { $this->id = $id; }
}
?>