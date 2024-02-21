<?php

namespace App\Exceptions;

class InvalidCreditCardException extends \Exception
{
    /* Attributes */
    private string $cardNumber;
    private string $expirationDate;
    private string $cardHolder;

    /* Constructor */
    public function __construct(string $cardNumber, string $expirationDate, string $cardHolder, string $reason = '')
    {
        parent::__construct('Invalid credit card, ' . $reason);

        $this->cardNumber = $cardNumber;
        $this->expirationDate = $expirationDate;
        $this->cardHolder = $cardHolder;
    }

    /* Methods */
    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    public function getExpirationDate(): string
    {
        return $this->expirationDate;
    }

    public function getCardHolder(): string
    {
        return $this->cardHolder;
    }
}