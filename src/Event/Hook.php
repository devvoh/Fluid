<?php

namespace Parable\Event;

class Hook
{
    /** @var array */
    protected $hooks = [];

    /**
     * Add the $callable to the list of hooks run when $event is triggered.
     *
     * @param string   $event
     * @param callable $callable
     *
     * @return $this
     */
    public function into($event, callable $callable)
    {
        $this->hooks[$event][] = $callable;
        return $this;
    }

    /**
     * Trigger $event and run through all hooks referenced, passing along $payload to all $callables.
     *
     * @param string     $event
     * @param null|mixed $payload
     *
     * @return $this
     */
    public function trigger($event, &$payload = null)
    {
        // Disallow calling a trigger on global hooks
        if ($event === '*') {
            return $this;
        }

        // Get all global hooks
        $globalHooks = [];
        if (isset($this->hooks['*']) && count($this->hooks['*']) > 0) {
            $globalHooks = $this->hooks['*'];
        }

        // Check if the event exists and has callables to run
        if (!isset($this->hooks[$event]) || count($this->hooks[$event]) === 0) {
            // There are no specific hooks, but maybe there's global hooks?
            if (count($globalHooks) === 0) {
                // There is nothing to do here
                return $this;
            }
            $hooks = $globalHooks;
        } else {
            $hooks = $this->hooks[$event];
            $hooks = array_merge($hooks, $globalHooks);
        }

        // All good, let's call those callables
        foreach ($hooks as $callable) {
            $callable($event, $payload);
        }
        return $this;
    }
}
