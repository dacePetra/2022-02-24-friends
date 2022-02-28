<?php

namespace App\Controllers;

use App\Database;
use App\Models\Article;
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
        session_start();
        $active = $_SESSION["name"];
        $activeId = $_SESSION["id"];
        return new View('Articles/index', [
            'articles' => $articles,
            'active'=>$active,
            'id' => $activeId
        ]);
    }

    public function show(array $vars): View
    {
        $articlesQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('articles')
            ->where('id = ?')
            ->setParameter(0, (int) $vars['id'])
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
        session_start();
        $active = $_SESSION["name"];
        $activeId = $_SESSION["id"];
        return new View('Articles/show', [
            'article' => $article,
            'active'=>$active,
            'id' => $activeId
        ]);
    }

    public function create(array $vars): View
    {
        session_start();
        $active = $_SESSION["name"];
        $activeId = $_SESSION["id"];
        return new View('Articles/create', [
            'active'=>$active,
            'id' => $activeId
        ]);
    }

    public function store(): Redirect
    {
        if(empty($_POST['title']) || empty($_POST['description'])){        //validate form, that it is not empty
            // Empty input
            return new Redirect('/articles/create');
        }
        session_start();
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
        Database::connection()->delete('articles', ['id'=> (int) $vars['id']]);
        // DELETE FROM articles WHERE id=?
        return new Redirect('/articles');
    }

    public function edit(array $vars): View
    {
        $articlesQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('articles')
            ->where('id = ?')
            ->setParameter(0, (int) $vars['id'])
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
        session_start();
        $active = $_SESSION["name"];
        $activeId = $_SESSION["id"];

        return new View('Articles/edit', [
            'article' => $article,
            'active'=>$active,
            'id' => $activeId
        ]);
    }

    public function update(array $vars): Redirect
    {
        //UPDATE articles SET title = ? AND description = ? WHERE id = ?
        Database::connection()->update('articles', [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
        ], ['id'=> (int)$vars['id']]);
        return new Redirect('/articles/'.$vars['id']);
    }

}