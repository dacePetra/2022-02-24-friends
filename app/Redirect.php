<?php

namespace App;

class Redirect
{
    public function __construct(string $location)
    {
        $this->location = $location;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

}