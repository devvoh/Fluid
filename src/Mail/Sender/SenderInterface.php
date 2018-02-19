<?php

namespace Parable\Mail\Sender;

interface SenderInterface
{
    /**
     * Send the mail using the implementation's logic.
     *
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param string $headers
     *
     * @return bool
     */
    public function send($to, $subject, $body, $headers);
}
