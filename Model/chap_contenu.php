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
        ?int $id = null,
        ?int $chapitreId = null,
        ?string $type = null,
        ?string $contenu = null,
        ?int $ordre = null
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

    public function setType($type)
    {
        $allowed = ["text", "image", "video", "pdf", "youtube"];

        if (!in_array($type, $allowed)) {
            throw new Exception("Type invalide");
        }

        $this->type = $type;
    }

    public function setContenu(?string $contenu): void {
        $this->contenu = $contenu;
    }

    public function setOrdre(?int $ordre): void {
        if ($ordre !== null && $ordre < 0) {
            throw new Exception("Ordre invalide");
        }
        $this->ordre = $ordre;
    }
}
?>