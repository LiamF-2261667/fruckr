<?php

namespace App\Exceptions;

/**
 * An exception that is thrown when no owner is found
 */
class NoOwnerException extends \Exception
{
    /* Constructors */
    /**
     * Create a new NoOwnerException
     * @param $message string the message to display
     */
    public function __construct($message = "No owner found")
    {
        parent::__construct($message, 0, null);
    }
}