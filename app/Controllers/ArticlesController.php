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
            ->orderBy('created_at', 'desc') //order by created_at desc
            ->executeQuery()
            ->fetchAllAssociative();

        //check if not null, then create object
        $articles = [];
        foreach ($articlesQuery as $articleData) {
            $articles [] = new Article(
                $articleData['title'],
                $articleData['description'],
                $articleData['created_at'],
                $articleData['id']
            );
        }
        return new View('Articles/index', [
            'articles' => $articles
        ]);
//        get information from database
//        create array with Article objects
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
            $articlesQuery['id']
        );
        return new View('Articles/show', [
            'article' => $article
        ]);
//  get information from database where article ID = $vars['id']
//  create Article object
//  give template for rendering
    }

    public function create(array $vars): View
    {
        return new View('Articles/create');
    }

    public function store(): Redirect
    {
        //validate form, that it is not empty
        Database::connection()
            ->insert('articles', [
                'title' => $_POST['title'],
                'description' => $_POST['description']
            ]);

        // redirect to /article
        return new Redirect('/articles');
        //header('Location: /articles');
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
            $articlesQuery['id']
        );

        return new View('Articles/edit', [
            'article' => $article
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