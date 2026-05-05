<?php
class Book {
    private ?int $id;
    private ?string $title;
    private ?string $author;
    private ?DateTime $publicationDate;
    private ?string $langue;
    private ?string $status;
    private ?int $copies;
    private ?string $category;

    //Constructor
    public function __construct(?int $id, ?string $title, ?string $author, ?DateTime $publicationDate, ?string $langue, ?string $status, ?int $copies, ?string $category) {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->publicationDate = $publicationDate;
        $this->langue = $langue;
        $this->status = $status;
        $this->copies = $copies;
        $this->category = $category;
    }

    public function show() {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Title</th><th>Author</th><th>Publication Date</th><th>Language</th><th>Status</th><th>Copies</th><th>Category</th></tr>";
        echo "<tr>";
        echo "<td>{$this->id}</td>";
        echo "<td>{$this->title}</td>";
        echo "<td>{$this->author}</td>";
        echo "<td>" . ($this->publicationDate ? $this->publicationDate->format('Y-m-d') : '') . "</td>";
        echo "<td>{$this->langue}</td>";
        echo "<td>{$this->status}</td>";
        echo "<td>{$this->copies}</td>";
        echo "<td>{$this->category}</td>";
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

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(?string $title): void {
        $this->title = $title;
    }

    public function getAuthor(): ?string {
        return $this->author;
    }

    public function setAuthor(?string $author): void {
        $this->author = $author;
    }

    public function getPublicationDate(): ?DateTime {
        return $this->publicationDate;
    }

    public function setPublicationDate(?DateTime $publicationDate): void {
        $this->publicationDate = $publicationDate;
    }

    public function getLangue(): ?string {
        return $this->langue;
    }

    public function setLangue(?string $langue): void {
        $this->langue = $langue;
    }

    public function getStatus(): ?string {
        return $this->status;
    }

    public function setStatus(?string $status): void {
        $this->status = $status;
    }

    public function getCopies(): ?int {
        return $this->copies;
    }

    public function setCopies(?int $copies): void {
        $this->copies = $copies;
    }

    public function getCategory(): ?string {
        return $this->category;
    }

    public function setCategory(?string $category): void {
        $this->category = $category;
    }
}
?>