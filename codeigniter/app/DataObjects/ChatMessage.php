<?php

namespace App\DataObjects;

use DateTime;

class ChatMessage
{
    /* Attributes */
    private DateTime $timestamp;
    private string $content;
    private bool $sendByClient;

    /* Constructors */
    /**
     * Create a new chat message
     * @param DateTime $timestamp The timestamp of the message
     * @param string $content The content of the message
     * @param bool $sendByClient Whether the message was sent by the client
     */
    public function __construct(DateTime $timestamp, string $content, bool $sendByClient)
    {
        $this->timestamp = $timestamp;
        $this->content = $content;
        $this->sendByClient = $sendByClient;
    }

    /* Getters */
    /**
     * Get the timestamp of the message
     * @return DateTime The timestamp of the message
     */
    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }

    /**
     * Get the formatted timestamp of the message
     * @return string The formatted timestamp of the message
     */
    public function getFormattedTimestamp(): string
    {
        return $this->timestamp->format('d/m/Y H:i');
    }

    public function getFormattedTime(): string
    {
        return $this->timestamp->format('H:i');
    }

    /**
     * Get the content of the message
     * @return string The content of the message
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Get whether the message was sent by the client
     * @return bool Whether the message was sent by the client
     */
    public function isSendByClient(): bool
    {
        return $this->sendByClient;
    }

    public function toHtml($fromClientView, $recipientName): string
    {
        if ($fromClientView)
            $sender = $this->sendByClient ? 'You' : $recipientName;
        else
            $sender = $this->sendByClient ? $recipientName : 'You';

        return '<div class="message-object ' . (($this->sendByClient == $fromClientView) ? 'you' : 'recipient') . '">
                    <p class="content">' . $this->content . '</p>
                    <p class="extra-info">' . $this->getFormattedTime() . ' - ' . $sender .'</p>
                </div>';
    }
}