<?php

namespace App\Exceptions;

/**
 * An exception that is thrown when no data is found in the database
 */
class NoDataException extends \Exception
{
    /* Constructors */
    /**
     * Create a new NoDataException
     * @param $message string the message to display
     */
    public function __construct(string $dataName, string $message = "Requested data not found: ")
    {
        parent::__construct($message . $dataName, 0, null);
    }
}