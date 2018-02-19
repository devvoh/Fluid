<?php

namespace Parable\Rights;

class Rights
{
    /** @var array */
    protected $rights = [];

    /**
     * Set up the default rights. Custom rights can be added at any point.
     */
    public function __construct()
    {
        $this->addRight('create');
        $this->addRight('read');
        $this->addRight('update');
        $this->addRight('delete');
    }

    /**
     * Add a right to the list. The correct value is calculated automatically.
     *
     * @param string $name
     *
     * @return $this
     */
    public function addRight($name)
    {
        $rights = $this->getRights();
        if (count($rights) == 0) {
            $value = 1;
        } else {
            $value = 2 * end($rights);
        }
        $this->rights[$name] = $value;
        return $this;
    }

    /**
     * Return all rights.
     *
     * @return array
     */
    public function getRights()
    {
        return $this->rights;
    }

    /**
     * Return a specific right by name.
     *
     * @param string $name
     *
     * @return int|false
     */
    public function getRight($name)
    {
        if (!isset($this->rights[$name])) {
            return false;
        }
        return $this->rights[$name];
    }

    /**
     * Check if binary number $provided has the right bit for right $name.
     *
     * @param string $provided
     * @param string $name
     *
     * @return bool
     */
    public function check($provided, $name)
    {
        $provided = bindec($provided);
        return (bool)($provided & $this->getRight($name));
    }

    /**
     * Combine all right values in $rights into a keep-high combined result.
     *
     * Takes an array of binary string values ([00011], [10011], ...])
     *
     * @param array $rights
     *
     * @return string
     */
    public function combine(array $rights)
    {
        $right_combined = str_repeat(0, count($this->rights));
        foreach ($rights as $right) {
            $right_combined |= $right;
        }
        return $right_combined;
    }

    /**
     * Turn an array of right names (["read", "create"]) into a binary string of rights ("0011").
     *
     * @param string[] $names
     *
     * @return string
     */
    public function getRightsFromNames(array $names)
    {
        $rights_string = "";
        foreach ($this->getRights() as $right => $value) {
            $rights_string .= in_array($right, $names) ? "1" : "0";
        }
        return strrev($rights_string);
    }

    /**
     * Turn a binary string of rights ("0011") into an array of right names (["read", "create"]).
     *
     * @param string $rights
     *
     * @return string[]
     */
    public function getNamesFromRights($rights)
    {
        $names = [];
        foreach ($this->rights as $name => $value) {
            if ($this->check($rights, $name)) {
                $names[] = $name;
            }
        }
        return $names;
    }
}
