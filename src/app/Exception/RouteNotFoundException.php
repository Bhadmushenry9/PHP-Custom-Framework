<?php

namespace App\Exception;

class RouteNotFoundException extends \Exception
{
     public function __construct($message) {
        parent::__construct("Invalid Route: $message");
    }
}
