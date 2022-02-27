<?php

namespace App\Controllers;

use App\Views\View;

class UsersController
{
    public function index(array $vars) //RESTful API, show all users
    {
        //var_dump('show all users');
        //get information from database
        //create array with Article objects
        return new View('Users/index.html', [
            'articles' => []
        ]);
    }

    public function show(array $vars)
    {
        // var_dump("show single user - {$input['id']}");
        //get information from database where article ID = $vars['id']
        // create Article object
        //give template for rendering

        return new View('Users/show.html', [
            'id'=>$vars['id']
        ]);
    }
}