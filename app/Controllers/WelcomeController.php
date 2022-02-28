<?php

namespace App\Controllers;

use App\Views\View;

class WelcomeController
{
    public function welcome(): View
    {
        return new View('welcome');
    }

}