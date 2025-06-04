<?php

namespace App\Exception;

class RequestNotFoundException extends \Exception
{
    protected $message = 'Page Not Found';
}
