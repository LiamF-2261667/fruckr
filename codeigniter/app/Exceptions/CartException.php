<?php

namespace App\Exceptions;

class CartException extends \Exception
{
    /* Attributes */

    /* Constructor */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    /* Methods */
}