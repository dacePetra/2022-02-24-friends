<?php

namespace App\Models;

class Comment
{
    private int $articleId;
    private string $createdAt;
    private string $author;
    private int $authorId;
    private string $text;
    private ?int $id = null;

    public function __construct(int $articleId, string $createdAt, string $author, int $authorId, string $text, ?int $id)
    {
        $this->articleId = $articleId;
        $this->createdAt = $createdAt;
        $this->author = $author;
        $this->authorId = $authorId;
        $this->text = $text;
        $this->id = $id;
    }

    public function getArticleId(): int
    {
        return $this->articleId;
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

    public function getText(): string
    {
        return $this->text;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
