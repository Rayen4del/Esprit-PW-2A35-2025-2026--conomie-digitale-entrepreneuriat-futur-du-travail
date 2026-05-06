<?php
// model/Profil.php
class Profil {
    private ?int $id;
    private ?int $idUtilisateur;
    private ?string $bio;
    private ?string $photo;
    private ?string $localisation;
    
    // Constructeur
    public function __construct(
        ?int $id = null,
        ?int $idUtilisateur = null,
        ?string $bio = null,
        ?string $photo = null,
        ?string $localisation = null
    ) {
        $this->id = $id;
        $this->idUtilisateur = $idUtilisateur;
        $this->bio = $bio;
        $this->photo = $photo;
        $this->localisation = $localisation;
    }
    
    // Getters
    public function getId(): ?int { return $this->id; }
    public function getIdUtilisateur(): ?int { return $this->idUtilisateur; }
    public function getBio(): ?string { return $this->bio; }
    public function getPhoto(): ?string { return $this->photo; }
    public function getLocalisation(): ?string { return $this->localisation; }
    
    // Setters
    public function setId(?int $id): void { $this->id = $id; }
    public function setIdUtilisateur(?int $idUtilisateur): void { $this->idUtilisateur = $idUtilisateur; }
    public function setBio(?string $bio): void { $this->bio = $bio; }
    public function setPhoto(?string $photo): void { $this->photo = $photo; }
    public function setLocalisation(?string $localisation): void { $this->localisation = $localisation; }
}
?>