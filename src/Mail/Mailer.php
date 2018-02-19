<?php

namespace Parable\Mail;

class Mailer
{
    /** @var \Parable\Mail\Sender\SenderInterface */
    protected $mailSender;

    /** @var array */
    protected $addresses = [
        'to'   => [],
        'cc'   => [],
        'bcc'  => [],
        'from' => [],
    ];

    /** @var string */
    protected $subject;

    /** @var string */
    protected $body;

    /** @var array */
    protected $requiredHeaders = [
        "MIME-Version: 1.0",
        "Content-type: text/html; charset=UTF-8",
    ];

    /** @var array */
    protected $headers = [];

    /**
     * Set the mail sender implementation to use.
     *
     * @param Sender\SenderInterface $mailSender
     *
     * @return $this
     */
    public function setMailSender(\Parable\Mail\Sender\SenderInterface $mailSender)
    {
        $this->mailSender = $mailSender;
        return $this;
    }

    /**
     * Return the mail sender implementation currently set.
     *
     * @return Sender\SenderInterface
     */
    public function getMailSender()
    {
        return $this->mailSender;
    }

    /**
     * Set the from address.
     *
     * @param string      $email
     * @param null|string $name
     *
     * @return $this
     * @throws \Parable\Mail\Exception
     */
    public function setFrom($email, $name = null)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Parable\Mail\Exception("Email provided is invalid: {$email}");
        }
        $this->addresses['from'] = [
            [
                'email' => $email,
                'name'  => $name,
            ]
        ];
        return $this;
    }

    /**
     * Add an address to send to.
     *
     * @param string      $email
     * @param string|null $name
     *
     * @return $this
     */
    public function addTo($email, $name = null)
    {
        $this->addAddress('to', $email, $name);
        return $this;
    }

    /**
     * Add an address to send to as CC.
     *
     * @param string      $email
     * @param string|null $name
     *
     * @return $this
     */
    public function addCc($email, $name = null)
    {
        $this->addAddress('cc', $email, $name);
        return $this;
    }

    /**
     * Add an address to send to as BCC.
     *
     * @param string      $email
     * @param string|null $name
     *
     * @return $this
     */
    public function addBcc($email, $name = null)
    {
        $this->addAddress('bcc', $email, $name);
        return $this;
    }

    /**
     * Add an address to the $type stack.
     *
     * @param string      $type
     * @param string      $email
     * @param null|string $name
     *
     * @throws \Parable\Mail\Exception
     */
    protected function addAddress($type, $email, $name = null)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Parable\Mail\Exception("Email provided is invalid: {$email}");
        }
        $this->addresses[$type][] = [
            'email' => $email,
            'name'  => $name,
        ];
    }

    /**
     * Return all addresses.
     *
     * @return array
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * Return all addresses for type.
     *
     * @param string $type
     *
     * @return string
     * @throws \Parable\Mail\Exception
     */
    public function getAddressesForType($type)
    {
        if (!isset($this->addresses[$type])) {
            throw new \Parable\Mail\Exception('Only to, cc, bcc addresses are allowed.');
        }

        $addresses = [];
        foreach ($this->addresses[$type] as $address) {
            if (!empty($address['name'])) {
                $addresses[] = $address['name'] . ' <' . $address['email'] . '>';
            } else {
                $addresses[] = $address['email'];
            }
        }
        return implode(', ', $addresses);
    }

    /**
     * Set the email subject.
     *
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Return the email subject.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set the email body.
     *
     * @param string $body
     *
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Return the email body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Add headers that are considered "required". These will not be cleared after a reset.
     *
     * @param string $header
     *
     * @return $this
     */
    public function addRequiredHeader($header)
    {
        $this->requiredHeaders[] = $header;
        return $this;
    }

    /**
     * Return all required headers.
     *
     * @return array
     */
    public function getRequiredHeaders()
    {
        return $this->requiredHeaders;
    }

    /**
     * Add regular headers. These will be cleared after a reset.
     *
     * @param string $header
     *
     * @return $this
     */
    public function addHeader($header)
    {
        $this->headers[] = $header;
        return $this;
    }

    /**
     * Return all regular headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Send the email.
     *
     * @return bool
     * @throws \Parable\Mail\Exception
     */
    public function send()
    {
        if (!$this->mailSender) {
            throw new \Parable\Mail\Exception('No mail sender implementation set.');
        }

        // Check the basics
        if (count($this->addresses['to']) === 0) {
            throw new \Parable\Mail\Exception('No to addresses provided.');
        }
        if (empty($this->subject)) {
            throw new \Parable\Mail\Exception('No subject provided.');
        }
        if (empty($this->body)) {
            throw new \Parable\Mail\Exception('No body provided.');
        }

        // Handle the to, cc and bcc addresses
        $to  = $this->getAddressesForType('to');
        $cc  = $this->getAddressesForType('cc');
        $bcc = $this->getAddressesForType('bcc');

        if (!empty($cc)) {
            $this->addHeader("Cc: {$cc}");
        }
        if (!empty($bcc)) {
            $this->addHeader("Bcc: {$bcc}");
        }

        // Handle from
        $from = $this->getAddressesForType('from');
        $this->addHeader("From: {$from}");

        $headers = array_merge($this->requiredHeaders, $this->headers);
        $headers = implode("\r\n", $headers);

        return $this->getMailSender()->send($to, $this->subject, $this->body, $headers);
    }

    /**
     * Actually send the email, using php's built-in mail() command.
     *
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param string $headers
     *
     * @return bool
     *
     * @codeCoverageIgnore
     */
    protected function sendMail($to, $subject, $body, $headers)
    {
        return mail(
            $to,
            $subject,
            $body,
            $headers
        );
    }

    /**
     * Reset just the subject, body, headers
     *
     * @return $this
     */
    public function resetMailData()
    {
        // Reset the mail values
        $this->subject           = null;
        $this->body              = null;
        $this->headers           = [];
        return $this;
    }

    /**
     * Reset all the addresses currently stored to receive
     * the e-mail.
     *
     * @return $this
     */
    public function resetRecipients()
    {
        $this->addresses['to']   = [];
        $this->addresses['cc']   = [];
        $this->addresses['bcc']  = [];
        return $this;
    }

    /**
     * Reset the sender email and name.
     *
     * @return $this
     */
    public function resetSender()
    {
        $this->addresses['from'] = [];
        return $this;
    }

    /**
     * Reset the class so it can be re-used for another, different
     * e-mail. This resets everything BUT the sender email and name.
     *
     * @return $this
     */
    public function reset()
    {
        $this->resetMailData();
        $this->resetRecipients();
        return $this;
    }
}
