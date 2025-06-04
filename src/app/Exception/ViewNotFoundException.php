<?php

namespace App\Exception;

class ViewNotFoundException extends \Exception
{
    public function __construct($viewFile) {
        parent::__construct("View file not found: $viewFile");
    }
}
