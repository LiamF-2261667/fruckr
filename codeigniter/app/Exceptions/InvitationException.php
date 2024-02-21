<?php

namespace App\Exceptions;

class InvitationException extends \Exception
{
    public function __construct($message = "")
    {
        parent::__construct($message);
    }
}