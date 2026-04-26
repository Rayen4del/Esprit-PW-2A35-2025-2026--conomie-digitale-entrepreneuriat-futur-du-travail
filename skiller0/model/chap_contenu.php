<?php
class ChapContenu {
    private ?int $id;
    private ?int $chapitreId;
    private ?string $type;
    private ?string $contenu;
    private ?int $ordre;

    // Types possibles (bonne pratique 🔥)
    const TYPE_TEXT = "text";
    const TYPE_VIDEO = "video";
    const TYPE_PDF = "pdf";

    // Constructor
    public function __construct(
        ?int $id,
        ?int $chapitreId,
        ?string $type,
        ?string $contenu,
        ?int $ordre
    ) {
        $this->id = $id;
        $this->chapitreId = $chapitreId;
        $this->type = $type;
        $this->contenu = $contenu;
        $this->ordre = $ordre;
    }

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getChapitreId(): ?int {
        return $this->chapitreId;
    }

    public function getType(): ?string {
        return $this->type;
    }

    public function getContenu(): ?string {
        return $this->contenu;
    }

    public function getOrdre(): ?int {
        return $this->ordre;
    }

    // Setters
    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setChapitreId(?int $chapitreId): void {
        $this->chapitreId = $chapitreId;
    }

    public function setType(?string $type): void {
        $typesValides = [self::TYPE_TEXT, self::TYPE_VIDEO, self::TYPE_PDF];

        if (!in_array($type, $typesValides)) {
            throw new Exception("Type invalide");
        }

        $this->type = $type;
    }

    public function setContenu(?string $contenu): void {
        $this->contenu = $contenu;
    }

    public function setOrdre(?int $ordre): void {
        if ($ordre < 0) {
            throw new Exception("Ordre invalide");
        }
        $this->ordre = $ordre;
    }
}
?>