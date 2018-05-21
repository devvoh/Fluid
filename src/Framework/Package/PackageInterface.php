<?php

namespace Parable\Framework\Package;

interface PackageInterface
{
    /**
     * Return an array of command class names.
     *
     * @return string[]
     */
    public function getCommands();

    /**
     * Return an array of init class names.
     *
     * @return string[]
     */
    public function getInits();
}
