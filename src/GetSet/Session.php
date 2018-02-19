<?php

namespace Parable\GetSet;

class Session extends \Parable\GetSet\Base
{
    /** @var string */
    protected $resource = '_SESSION';

    /**
     * Start the session.
     *
     * @return $this
     * @throws \Parable\GetSet\Exception
     */
    public function start()
    {
        if (headers_sent()) {
            throw new \Parable\GetSet\Exception("Headers already sent, can't start the session.");
        }

        session_start();
        return $this;
    }

    /**
     * Regenerate the session id.
     *
     * @param bool $deleteOldSession
     *
     * @return $this
     */
    public function regenerateId($deleteOldSession = false)
    {
        session_regenerate_id($deleteOldSession);
        return $this;
    }

    /**
     * Destroy the current session.
     *
     * @return $this
     */
    public function destroy()
    {
        session_destroy();
        return $this;
    }
}
