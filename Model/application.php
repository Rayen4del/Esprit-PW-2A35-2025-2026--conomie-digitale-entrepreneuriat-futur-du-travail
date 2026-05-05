<?php
class Application {
    private ?int $id;
    private ?int $idUtilisateur;
    private ?int $idOportunity;
    private ?DateTime $dateCondidature;
    private ?string $statut;
    private ?string $motivation;
    private ?string $resource;

    // Constructor
    public function __construct(
        ?int $id,
        ?int $idUtilisateur,
        ?int $idOportunity,
        ?DateTime $dateCondidature,
        ?string $statut,
        ?string $motivation,
        ?string $resource
    ) {
        $this->id = $id;
        $this->idUtilisateur = $idUtilisateur;
        $this->idOportunity = $idOportunity;
        $this->dateCondidature = $dateCondidature;
        $this->statut = $statut;
        $this->motivation = $motivation;
        $this->resource = $resource;
    }

    public function show() {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>
                <th>ID</th>
                <th>ID Utilisateur</th>
                <th>ID Opportunity</th>
                <th>Date Candidature</th>
                <th>Statut</th>
                <th>Motivation</th>
                <th>Resource</th>
              </tr>";
        echo "<tr>";
        echo "<td>{$this->id}</td>";
        echo "<td>{$this->idUtilisateur}</td>";
        echo "<td>{$this->idOportunity}</td>";
        echo "<td>" . ($this->dateCondidature ? $this->dateCondidature->format('Y-m-d') : '') . "</td>";
        echo "<td>{$this->statut}</td>";
        echo "<td>{$this->motivation}</td>";
        echo "<td>{$this->resource}</td>";
        echo "</tr>";
        echo "</table>";
    }

    // Getters and Setters

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function getIdUtilisateur(): ?int {
        return $this->idUtilisateur;
    }

    public function setIdUtilisateur(?int $idUtilisateur): void {
        $this->idUtilisateur = $idUtilisateur;
    }

    public function getIdOportunity(): ?int {
        return $this->idOportunity;
    }

    public function setIdOportunity(?int $idOportunity): void {
        $this->idOportunity = $idOportunity;
    }

    public function getDateCondidature(): ?DateTime {
        return $this->dateCondidature;
    }

    public function setDateCondidature(?DateTime $dateCondidature): void {
        $this->dateCondidature = $dateCondidature;
    }

    public function getStatut(): ?string {
        return $this->statut;
    }

    public function setStatut(?string $statut): void {
        $this->statut = $statut;
    }

    public function getMotivation(): ?string {
        return $this->motivation;
    }

    public function setMotivation(?string $motivation): void {
        $this->motivation = $motivation;
    }

    public function getResource(): ?string {
        return $this->resource;
    }

    public function setResource(?string $resource): void {
        $this->resource = $resource;
    }
}
?>