<?php

namespace App\Controllers;

use App\Database;
use App\Redirect;

class ArticleCommentsController
{
    public function comment(array $vars): Redirect
    {
        $active = $_SESSION["name"];
        $activeId = $_SESSION["id"];
//      check if not empty
        Database::connection()
            ->insert('article_comments', [
                'article_id' => (int)$vars['id'],
                'author' => $active,
                'author_id' => $activeId,
                'text' => $_POST['comment']
            ]);
        return new Redirect('/articles/'.$vars['id']);
    }
    public function erase(array $vars): Redirect
    {
        Database::connection()
            ->delete('article_comments', ['id'=> (int) $vars['nr']]);
        return new Redirect('/articles/'.$vars['id']);
    }

}