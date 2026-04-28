<?php
class Produit {
    private ?int $id_p;
    private ?string $nom;
    private ?string $categrie;
    private ?float $prix;
    private ?string $description;
    private ?string $image;
    private ?int $id_sp;

    // Constructor
    public function __construct(
        ?int $id_p,
        ?string $nom,
        ?string $categrie,
        ?float $prix,
        ?string $description,
        ?string $image,
        ?int $id_sp
    ) {
        $this->id_p = $id_p;
        $this->nom = $nom;
        $this->categrie = $categrie;
        $this->prix = $prix;
        $this->description = $description;
        $this->image = $image;
        $this->id_sp = $id_sp;
    }

    public function show() {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>
                <th>ID Produit</th>
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Prix</th>
                <th>Description</th>
                <th>Image</th>
                <th>ID Sponsoring</th>
              </tr>";
        echo "<tr>";
        echo "<td>{$this->id_p}</td>";
        echo "<td>{$this->nom}</td>";
        echo "<td>{$this->categrie}</td>";
        echo "<td>{$this->prix}</td>";
        echo "<td>{$this->description}</td>";
        echo "<td>{$this->image}</td>";
        echo "<td>{$this->id_sp}</td>";
        echo "</tr>";
        echo "</table>";
    }

    // Getters and Setters

    public function getIdP(): ?int {
        return $this->id_p;
    }

    public function setIdP(?int $id_p): void {
        $this->id_p = $id_p;
    }

    public function getNom(): ?string {
        return $this->nom;
    }

    public function setNom(?string $nom): void {
        $this->nom = $nom;
    }

    public function getCategrie(): ?string {
        return $this->categrie;
    }

    public function setCategrie(?string $categrie): void {
        $this->categrie = $categrie;
    }

    public function getPrix(): ?float {
        return $this->prix;
    }

    public function setPrix(?float $prix): void {
        $this->prix = $prix;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getImage(): ?string {
        return $this->image;
    }

    public function setImage(?string $image): void {
        $this->image = $image;
    }

    public function getIdSp(): ?int {
        return $this->id_sp;
    }

    public function setIdSp(?int $id_sp): void {
        $this->id_sp = $id_sp;
    }
}
?>