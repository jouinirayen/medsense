<?php
class commentaire {
    private $id;
    private $blog_id;
    private $contenu;
    private $utilisateur_id;  
    private $created_at;

    public function __construct($contenu, $blog_id, $utilisateur_id) {
        $this->contenu = $contenu;
        $this->blog_id = $blog_id;
        $this->utilisateur_id = $utilisateur_id;
    }

    public function getId() { return $this->id; }
    public function getBlogId() { return $this->blog_id; }
    public function getContenu() { return $this->contenu; }
    public function getUtilisateurId() { return $this->utilisateur_id; }
    public function getCreatedAt() { return $this->created_at; }

    public function setId($id) { $this->id = $id; }
    public function setCreatedAt($date) { $this->created_at = $date; }
}
?>