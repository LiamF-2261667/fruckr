<?php

namespace App\Helpers;

use App\Exceptions\InvalidCreditCardException;

class CreditCardValidator
{
    /* Methods */
    /**
     * Validate a credit card number
     * @param string $cardNumber The card number to validate
     * @param string $expirationDate The expiration date of the card
     * @param string $cardHolder The cardholder's name
     * @throws InvalidCreditCardException If the card is invalid
     */
    public static function validateCardNumber(string $cardNumber, string $expirationDate, string $cardHolder): void
    {
        // Remove all non-numeric characters
        $cardNumber = preg_replace('/\D/', '', $cardNumber);

        // The card number has to be the correct length
        if (strlen($cardNumber) < 16 || strlen($cardNumber) > 24)
            throw new InvalidCreditCardException($cardNumber, $expirationDate, $cardHolder, 'The card number is not the correct length');

        // The card number has to be valid
        $cardNumberRegex = '/^[a-zA-Z]{0,2}[0-9\- ]+[a-zA-Z]?\d?$/';
        if (!preg_match($cardNumberRegex, $cardNumber))
            throw new InvalidCreditCardException($cardNumber, $expirationDate, $cardHolder, 'The card number is not valid');

        // The cardholder has to be valid
        $cardHolderRegex = '/^[a-zA-Z\- ]+$/';
        if (!preg_match($cardHolderRegex, $cardHolder))
            throw new InvalidCreditCardException($cardNumber, $expirationDate, $cardHolder, 'The card holder is not valid');

        // The expiration date has to be valid
        $expirationDateRegex = '/^(0[1-9]|1[0-2])\/?([0-9]{2})$/';
        if (!preg_match($expirationDateRegex, $expirationDate))
            throw new InvalidCreditCardException($cardNumber, $expirationDate, $cardHolder, 'The expiration date is not valid');
    }
}