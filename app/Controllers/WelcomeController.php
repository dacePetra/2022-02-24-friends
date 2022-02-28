<?php

namespace App\Controllers;

use App\Views\View;

class WelcomeController
{
    public function opening(): View
    {
        return new View('opening');
    }

    public function welcome(): View
    {
        session_start();
        $active = $_SESSION["name"];
        $activeId = $_SESSION["id"];
        return new View('welcome', [
            'active' => $active,
            'id' => $activeId
        ]);
    }

}