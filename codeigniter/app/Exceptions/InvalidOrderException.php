<?php

namespace App\Exceptions;

/**
 * Exception thrown when an order is invalid
 */
class InvalidOrderException extends \Exception
{
    /* Attributes */

    /* Initializers */
    public function __construct(string $msg)
    {
        parent::__construct($msg);
    }

    /* Methods */
}