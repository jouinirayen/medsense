<?php
/**
 * Service Model
 * Représente un service avec ses attributs
 */

class ServiceModel {
    
    // Attributs de la classe
    private $id;
    private $name;
    private $description;
    private $icon;
    private $link;
    private $image;
    
  
    public function __construct($id = null, $name = '', $description = '', $icon = '', $link = '', $image = '') {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->icon = $icon;
        $this->link = $link;
        $this->image = $image;
    }
    
    // Getters
    public function getId() {
        return $this->id;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function getIcon() {
        return $this->icon;
    }
    
    public function getLink() {
        return $this->link;
    }
    
    public function getImage() {
        return $this->image;
    }
    
    public function setId($id) {
        $this->id = $id;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function setDescription($description) {
        $this->description = $description;
    }
    
    public function setIcon($icon) {
        $this->icon = $icon;
    }
    
    public function setLink($link) {
        $this->link = $link;
    }
    
    public function setImage($image) {
        $this->image = $image;
    }
    
    
    public function toArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'link' => $this->link,
            'image' => $this->image
        ];
    }
}
?>
