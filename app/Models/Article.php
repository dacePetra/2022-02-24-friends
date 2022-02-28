<?php

namespace App\Models;

class Article
{
    private string $title;
    private string $description;
    private string $createdAt;
    private string $author;
    private int $authorId;
    private ?int $id = null;

    public function __construct(string $title, string $description, string $createdAt, string $author, int $authorId,?int $id = null)
    {
        $this->title = $title;
        $this->description = $description;
        $this->createdAt = $createdAt;
        $this->author = $author;
        $this->authorId = $authorId;
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getAuthorId(): int
    {
        return $this->authorId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}