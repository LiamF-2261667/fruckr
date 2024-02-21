<?php

namespace App\Exceptions;

/**
 * An exception that is thrown when no user is found
 */
class NoUserException extends \Exception
{
    /* Constructors */
    /**
     * Create a new NoUserException
     * @param $message string the message to display
     */
    public function __construct($message = "No user found")
    {
        parent::__construct($message, 0, null);
    }
}