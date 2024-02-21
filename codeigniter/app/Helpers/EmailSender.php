<?php

namespace App\Helpers;

use App\DataObjects\EmailData;
use App\Exceptions\MailException;
use CodeIgniter\Email\Email;

class EmailSender
{
    private static string $email = 'froyenliam@gmail.com';
    private static string $name = 'Fruckr';

    /**
     * Send email
     * @param EmailData $emailData the email data
     * @throws MailException if email could not be sent
     */
    public static function sendMail(EmailData $emailData)
    {
        log_message('debug', 'Sending email to ' . $emailData->getRecipientEmail() . ' with subject ' . $emailData->getSubject() . ' and message ' . $emailData->getMessage());

        // Send email
        $email = \Config\Services::email();

        $email->setFrom(self::$email, self::$name);
        $email->setTo($emailData->getRecipientEmail());

        $email->setSubject($emailData->getSubject());
        $email->setMessage(self::getHeader() . $emailData->getMessage() . self::getFooter());

        if (!$email->send())
            throw new MailException();
    }

    private static function getHeader(): string
    {
        return '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Fruckr</title>
                ' . self::getStyling() . '
            </head>
            <body>
                <header>
                    <h1>Fruckr</h1>
                </header>
                <div class="main">
        ';
    }

    private static function getFooter(): string
    {
        return '
                </div>
                <footer>
                    <p>Kind regards,</p>
                    <p>Fruckr</p>
                    <a style="color: white" href="'. base_url() .'" target="_blank">Fruckr Website</a>
                </footer>
            </body>
            </html>
        ';
    }

    private static function getStyling(): string
    {
        return '<style>
            h1 {
                font-family: Georgia, serif;
                font-size: 3rem;
                font-weight: bold;
            }
            
            header {
                text-align: center;
                color: white;
                background-color: IndianRed;
                
                /* Box */
                padding: 1rem;
                padding-top: 1.5rem;
                padding-bottom: 1.5rem;
                width: 90%;
            }
            
            .main {
                /* Box */
                padding: 1rem;
                margin-left: 5rem;
                margin-right: 5rem;
            }
            
            footer {
                text-align: center;
                background-color: rgb(148, 64, 64);
                color: white;
                
                /* Box */
                padding: 1rem;
                padding-bottom: 2.5rem;
                width: 90%;
            }
        </style>';
    }
}