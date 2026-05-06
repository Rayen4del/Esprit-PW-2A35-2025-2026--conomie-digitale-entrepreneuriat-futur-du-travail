<?php
// model/User.php
class User {
    private ?int $id;
    private ?string $nom;
    private ?string $email;
    private ?string $mdp;
    private ?string $type;
    private ?string $statut;
    private ?string $created_at;
    
    // Constructeur
    public function __construct(
        ?int $id = null,
        ?string $nom = null,
        ?string $email = null,
        ?string $mdp = null,
        ?string $type = 'etudiant',
        ?string $statut = 'actif',
        ?string $created_at = null
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->email = $email;
        $this->mdp = $mdp;
        $this->type = $type;
        $this->statut = $statut;
        $this->created_at = $created_at;
    }
    
    // Getters
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function getEmail(): ?string { return $this->email; }
    public function getMdp(): ?string { return $this->mdp; }
    public function getType(): ?string { return $this->type; }
    public function getStatut(): ?string { return $this->statut; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    
    // Setters
    public function setId(?int $id): void { $this->id = $id; }
    public function setNom(?string $nom): void { $this->nom = $nom; }
    public function setEmail(?string $email): void { $this->email = $email; }
    public function setMdp(?string $mdp): void { $this->mdp = $mdp; }
    public function setType(?string $type): void { $this->type = $type; }
    public function setStatut(?string $statut): void { $this->statut = $statut; }
    public function setCreatedAt(?string $created_at): void { $this->created_at = $created_at; }
    
    // Méthode pour afficher (optionnelle)
    public function show() {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Email</th><th>Type</th><th>Statut</th></tr>";
        echo "<tr>";
        echo "<td>{$this->id}</td>";
        echo "<td>{$this->nom}</td>";
        echo "<td>{$this->email}</td>";
        echo "<td>{$this->type}</td>";
        echo "<td>{$this->statut}</td>";
        echo "</tr>";
        echo "</table>";
    }
}
?>