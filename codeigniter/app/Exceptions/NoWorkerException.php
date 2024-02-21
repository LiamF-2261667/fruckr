<?php

namespace App\Exceptions;

/**
 * An exception that is thrown when no worker is found
 */
class NoWorkerException extends \Exception
{
    /* Constructors */
    /**
     * Create a new NoWorkerException
     * @param $message string the message to display
     */
    public function __construct($message = "No worker found")
    {
        parent::__construct($message, 0, null);
    }
}