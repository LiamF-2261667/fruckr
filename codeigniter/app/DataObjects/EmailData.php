<?php

namespace App\DataObjects;

class EmailData
{
    /* Attributes */
    private string $recipientEmail;
    private string $subject;
    private string $message;

    /* Constructor */
    public function __construct(string $recipientEmail, string $subject, string $message)
    {
        $this->recipientEmail = $recipientEmail;
        $this->subject = $subject;
        $this->message = $message;
    }

    /* Getters */
    public function getRecipientEmail(): string
    {
        return $this->recipientEmail;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}