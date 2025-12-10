<?php
// Model/blog.php

class publication {
    private $contenu;
    private $imageUrl;
    private $createdAt;
    private $utilisateur_id;  // New property for the user ID

    // Updated constructor: Accepts 4 params, with $utilisateur_id optional for updates
    public function __construct($contenu, $imageUrl, $createdAt, $utilisateur_id = null) {
        $this->contenu = $contenu;
        $this->imageUrl = $imageUrl;
        $this->createdAt = $createdAt;
        $this->utilisateur_id = $utilisateur_id;
    }

    // Getter for contenu
    public function getContenu() {
        return $this->contenu;
    }

    // Getter for imageUrl
    public function getImageUrl() {
        return $this->imageUrl;
    }

    // Getter for createdAt
    public function getCreatedAt() {
        return $this->createdAt;
    }

    // New getter for utilisateur_id (fixes the error)
    public function getUtilisateurId() {
        return $this->utilisateur_id;
    }

    // Optional: Add a setter if you need to update user_id later (not required for your current code)
    public function setUtilisateurId($utilisateur_id) {
        $this->utilisateur_id = $utilisateur_id;
    }
}
?>