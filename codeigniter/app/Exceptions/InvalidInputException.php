<?php

namespace App\Exceptions;

/**
 * An exception that is thrown when an input is invalid
 */
class InvalidInputException extends \Exception
{
    /* Attributes */
    private string $inputName;

    /* Constructors */
    /**
     * Create a new InvalidInputException
     * @param $inputName string the name of the input that is invalid
     * @param $message string the message to display
     */
    public function __construct($inputName, $message = "Invalid input")
    {
        parent::__construct($message, 0, null);
    }

    /* Methods */
    /**
     * Get the name of the input that is invalid
     * @return string the name of the input that is invalid
     */
    public function getInputName(): string
    {
        return $this->inputName;
    }
}