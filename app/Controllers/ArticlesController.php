<?php

namespace App\Controllers;

use App\Database;
use App\Models\Article;
use App\Models\Comment;
use App\Redirect;
use App\Views\View;

class ArticlesController
{
    public function index(): View
    {
        $articlesQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('articles')
            ->orderBy('created_at', 'desc')
            ->executeQuery()
            ->fetchAllAssociative();

        //check if not null, then create object
        $articles = [];
        foreach ($articlesQuery as $articleData) {
            $articles [] = new Article(
                $articleData['title'],
                $articleData['description'],
                $articleData['created_at'],
                $articleData['author'],
                $articleData['author_id'],
                $articleData['id']
            );
        }
        $active = $_SESSION["name"];
        $activeId = $_SESSION["id"];
        return new View('Articles/index', [
            'articles' => $articles,
            'active' => $active,
            'id' => $activeId
        ]);
    }

    public function show(array $vars): View
    {
        $active = $_SESSION["name"];
        $activeId = $_SESSION["id"];
        $articleId = (int)$vars['id'];
        $articlesQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('articles')
            ->where('id = ?')
            ->setParameter(0, $articleId)
            ->executeQuery()
            ->fetchAssociative();

        $article = new Article(
            $articlesQuery['title'],
            $articlesQuery['description'],
            $articlesQuery['created_at'],
            $articlesQuery['author'],
            $articlesQuery['author_id'],
            $articlesQuery['id']
        );

        $articleLikesQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('article_likes')
            ->where('article_id = ?')
            ->setParameter(0, $articleId)
            ->executeQuery()
            ->fetchAllAssociative();

        $likerList = [];
        foreach ($articleLikesQuery as $entry) {
            $likerList [] = $entry['user_id'];
        }
        $liked = in_array($activeId, $likerList);
        $articleLikes = count($likerList);

        $commentsQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('article_comments')
            ->where('article_id = ?')
            ->setParameter(0, $articleId)
            ->orderBy('created_at', 'desc')
            ->executeQuery()
            ->fetchAllAssociative();

        //check if not null, then create object
        $comments = [];
        foreach ($commentsQuery as $commentData) {
            $comments [] = new Comment(
                $commentData['article_id'],
                $commentData['created_at'],
                $commentData['author'],
                $commentData['author_id'],
                $commentData['text'],
                $commentData['id']
            );
        }
        $numberOfComments = count($comments);

        return new View('Articles/show', [
            'article' => $article,
            'comments' => $comments,
            'articleLikes' => $articleLikes,
            'numberOfComments' => $numberOfComments,
            'active' => $active,
            'id' => $activeId,
            'liked' => $liked
        ]);
    }

    public function create(array $vars): View
    {
        $inputTitle = $_SESSION["inputTitle"];
        $inputDescription = $_SESSION["inputDescription"];
        $active = $_SESSION["name"];
        $activeId = $_SESSION["id"];
        return new View('Articles/create', [
            'active' => $active,
            'id' => $activeId,
            'inputTitle' => $inputTitle,
            'inputDescription' => $inputDescription
        ]);
    }

    public function store(): Redirect
    {
        if (empty($_POST['title']) || empty($_POST['description'])) {  //validate form - it is not empty
            $_SESSION["inputTitle"] = $_POST['title'];
            $_SESSION["inputDescription"] = $_POST['description'];
            return new Redirect('/articles/create');
        }
        $active = $_SESSION["name"];
        $activeId = $_SESSION["id"];

        Database::connection()
            ->insert('articles', [
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'author' => $active,
                'author_id' => $activeId
            ]);

        return new Redirect('/articles');
    }

    public function delete(array $vars): Redirect
    {
        Database::connection()
            ->delete('articles', ['id' => (int)$vars['id']]);
        return new Redirect('/articles');
    }

    public function edit(array $vars): View
    {
        $articlesQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('articles')
            ->where('id = ?')
            ->setParameter(0, (int)$vars['id'])
            ->executeQuery()
            ->fetchAssociative();

        $article = new Article(
            $articlesQuery['title'],
            $articlesQuery['description'],
            $articlesQuery['created_at'],
            $articlesQuery['author'],
            $articlesQuery['author_id'],
            $articlesQuery['id']
        );
        $active = $_SESSION["name"];
        $activeId = $_SESSION["id"];

        return new View('Articles/edit', [
            'article' => $article,
            'active' => $active,
            'id' => $activeId
        ]);
    }

    public function update(array $vars): Redirect
    {
        Database::connection()
            ->update('articles', [
                'title' => $_POST['title'],
                'description' => $_POST['description'],
            ], ['id' => (int)$vars['id']]
            );
        return new Redirect('/articles/' . $vars['id']);
    }

    public function like(array $vars): Redirect
    {
        $activeId = $_SESSION["id"];
        $articleId = (int)$vars['id'];
        $userId = $activeId;
        $articleLikesQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('article_likes')
            ->where('article_id = ?')
            ->setParameter(0, $articleId)
            ->executeQuery()
            ->fetchAllAssociative();

        $likerList = [];
        foreach ($articleLikesQuery as $entry) {
            $likerList [] = $entry['user_id'];
        }
        if (!in_array($activeId, $likerList)) {
            Database::connection()->insert('article_likes', [
                'article_id' => $articleId,
                'user_id' => $userId
            ]);
        }
        return new Redirect("/articles/{$articleId}");
    }

}