<?php

namespace Parable\Mail\Sender;

class PhpMail implements \Parable\Mail\Sender\SenderInterface
{
    /**
     * Send using php's built-in Mail interface
     *
     * @inheritdoc
     *
     * @codeCoverageIgnore
     */
    public function send($to, $subject, $body, $headers)
    {
        return mail(
            $to,
            $subject,
            $body,
            $headers
        );
    }
}
