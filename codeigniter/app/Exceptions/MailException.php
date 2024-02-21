<?php

namespace App\Exceptions;

class MailException extends \Exception
{
    public function __construct(string $message = 'Mail could not be sent')
    {
        parent::__construct($message);
    }
}