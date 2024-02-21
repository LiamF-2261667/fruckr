<?php

namespace App\Exceptions;

class ChatException extends \Exception
{
    public function __construct($message = "")
    {
        parent::__construct($message);
    }
}