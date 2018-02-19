<?php

namespace Parable\Framework;

class SessionMessage
{
    const SESSION_KEY = "parable_session_messages";

    /** @var \Parable\GetSet\Session */
    protected $session;

    /** @var array */
    protected $messages = [];

    public function __construct(\Parable\GetSet\Session $session)
    {
        $this->session = $session;

        $this->readFromSession();
    }

    /**
     * Return all messages or all messages of $type.
     *
     * @param null|string $type
     *
     * @return array
     */
    public function get($type = null)
    {
        if (!$type) {
            return $this->messages;
        }

        if (isset($this->messages[$type])) {
            return $this->messages[$type];
        }

        return [];
    }

    /**
     * Return all messages or all messages of $type and then clear those messages.
     *
     * @param null|string $type
     *
     * @return array
     */
    public function getClear($type = null)
    {
        $messages = $this->get($type);
        $this->clear($type);
        return $messages;
    }

    /**
     * Add a message to type notice by default, or to $type instead.
     *
     * @param null|string $message
     * @param string      $type
     *
     * @return $this
     */
    public function add($message = null, $type = 'notice')
    {
        if (!isset($this->messages[$type]) || !is_array($this->messages[$type])) {
            $this->messages[$type] = [];
        }
        if ($message) {
            $this->messages[$type][] = $message;
        }
        $this->writeToSession();
        return $this;
    }

    /**
     * Clear all messages or all messages of $type.
     *
     * @param null|string $type
     *
     * @return $this
     */
    public function clear($type = null)
    {
        if (!$type) {
            $this->messages = [];
        } elseif (isset($this->messages[$type])) {
            unset($this->messages[$type]);
        }

        $this->writeToSession();
        return $this;
    }

    /**
     * Count all messages or all messages of $type.
     *
     * @param null|string $type
     *
     * @return int
     */
    public function count($type = null)
    {
        if ($type) {
            return count($this->get($type));
        }

        $count = 0;
        foreach ($this->get() as $type => $messages) {
            $count += count($messages);
        }
        return $count;
    }

    /**
     * Read messages stored in the session and load them into SessionMessage.
     *
     * @return $this
     */
    protected function readFromSession()
    {
        if (is_array($this->session->get(self::SESSION_KEY))) {
            $this->messages = $this->session->get(self::SESSION_KEY);
        }
        return $this;
    }

    /**
     * Write messages stored on SessionMessage to the session.
     *
     * @return $this
     */
    protected function writeToSession()
    {
        $this->session->set(self::SESSION_KEY, $this->messages);
        return $this;
    }
}
