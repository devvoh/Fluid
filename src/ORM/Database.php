<?php

namespace Parable\ORM;

class Database
{
    /** Use this to set a value to SQL NULL */
    const NULL_VALUE = '__parable_null_value__';

    /** Types supported */
    const TYPE_SQLITE = 'sqlite';
    const TYPE_MYSQL  = 'mysql';

    /** SQLite-specific location */
    const LOCATION_SQLITE_MEMORY = ':memory:';

    /** @var null|string */
    protected $type;

    /** @var null|string */
    protected $location;

    /** @var null|string */
    protected $username;

    /** @var null|string */
    protected $password;

    /** @var null|string */
    protected $database;

    /** @var null|string */
    protected $charset;

    /** @var null|\PDO */
    protected $instance;

    /** @var int */
    protected $errorMode = \PDO::ERRMODE_SILENT;

    /** @var bool */
    protected $softQuoting = true;

    /**
     * Returns the type, if any.
     *
     * @return null|string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the type.
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Returns the location, if any.
     *
     * @return null|string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set the location.
     *
     * @param string $location
     *
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * Return the username.
     *
     * @return null|string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the username.
     *
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Return the password.
     *
     * @return null|string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the password.
     *
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Return the database, if any.
     *
     * @return null|string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Set the database.
     *
     * @param string $database
     *
     * @return $this
     */
    public function setDatabase($database)
    {
        $this->database = $database;
        return $this;
    }

    /**
     * Return the charset, if set.
     *
     * @return null|string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Set the charset for the database connection; if not set, database setting is used.
     *
     * @param null|string $charset
     *
     * @return Database
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * Return the error mode.
     *
     * @return int
     */
    public function getErrorMode()
    {
        return $this->errorMode;
    }

    /**
     * Set the error mode.
     *
     * @param int $errorMode
     *
     * @return $this
     */
    public function setErrorMode($errorMode)
    {
        if (in_array($errorMode, [\PDO::ERRMODE_SILENT, \PDO::ERRMODE_WARNING, \PDO::ERRMODE_EXCEPTION])) {
            $this->errorMode = $errorMode;
        }
        return $this;
    }

    /**
     * Return whether soft quoting is enabled or not.
     *
     * @return bool
     */
    public function getSoftQuoting()
    {
        return $this->softQuoting;
    }

    /**
     * Set whether to allow soft quoting or not.
     *
     * @param bool $value
     *
     * @return $this
     */
    public function setSoftQuoting($value)
    {
        $this->softQuoting = (bool)$value;
        return $this;
    }

    /**
     * Return the instance, and start one if needed.
     *
     * @return null|\PDO
     * @throws \Parable\ORM\Exception
     */
    public function getInstance()
    {
        if (!$this->instance && $this->getType() && $this->getLocation()) {
            switch ($this->getType()) {
                case static::TYPE_SQLITE:
                    $instance = $this->createPDOSQLite(
                        $this->getLocation(),
                        $this->getErrorMode()
                    );
                    $this->setInstance($instance);
                    break;
                case static::TYPE_MYSQL:
                    if (!$this->getUsername() || !$this->getPassword() || !$this->getDatabase()) {
                        return null;
                    }
                    $instance = $this->createPDOMySQL(
                        $this->getLocation(),
                        $this->getDatabase(),
                        $this->getUsername(),
                        $this->getPassword(),
                        $this->getErrorMode(),
                        $this->getCharSet()
                    );
                    $this->setInstance($instance);
                    break;
                default:
                    throw new \Parable\ORM\Exception("Database type was invalid: {$this->getType()}");
            }
        }
        return $this->instance;
    }

    /**
     * Create and return a sqlite PDO instance.
     *
     * @param string $location
     * @param int    $errorMode
     *
     * @return \Parable\ORM\Database\PDOSQLite
     */
    protected function createPDOSQLite($location, $errorMode)
    {
        $dsn = 'sqlite:' . $location;

        $db  = new \Parable\ORM\Database\PDOSQLite($dsn);
        $db->setAttribute(\PDO::ATTR_ERRMODE, $errorMode);

        return $db;
    }

    /**
     * Create and return a MySQL PDO instance.
     *
     * @param string $location
     * @param string $database
     * @param string $username
     * @param string $password
     * @param int    $errorMode
     * @param string $charset
     *
     * @return \Parable\ORM\Database\PDOMySQL
     *
     * @codeCoverageIgnore
     */
    protected function createPDOMySQL($location, $database, $username, $password, $errorMode, $charset)
    {
        $dsn = 'mysql:host=' . $location . ';dbname=' . $database;
        if ($charset !== null) {
            $dsn .= ';charset=' . $charset;
        }

        $db  = new \Parable\ORM\Database\PDOMySQL($dsn, $username, $password);
        $db->setAttribute(\PDO::ATTR_ERRMODE, $errorMode);

        return $db;
    }

    /**
     * Sets the instance.
     *
     * @param \PDO $instance
     *
     * @return $this
     */
    public function setInstance(\PDO $instance)
    {
        $this->instance = $instance;
        return $this;
    }

    /**
     * If an instance is available, quote/escape the message through PDOs quote function.
     * If not and soft quoting is enabled, fudge it.
     *
     * @param string $string
     *
     * @return null|string
     * @throws \Parable\ORM\Exception
     */
    public function quote($string)
    {
        if (!$this->getInstance()) {
            if (!$this->softQuoting) {
                throw new \Parable\ORM\Exception("Can't quote without a database instance.");
            }

            $string = str_replace("'", "", $string);
            return "'{$string}'";
        }
        return $this->getInstance()->quote($string);
    }

    /**
     * Identifiers need to be escaped differently than values, using ` characters. PDO by default does not offer this.
     *
     * @param string $string
     *
     * @return string
     */
    public function quoteIdentifier($string)
    {
        return '`' . (string)$string . '`';
    }

    /**
     * Passes $query on to the PDO instance if it's successfully initialized. If not, returns false. If so, returns
     * PDO result object.
     *
     * @param string $query
     *
     * @return \PDOStatement
     * @throws \Parable\ORM\Exception
     */
    public function query($query)
    {
        if (!$this->getInstance()) {
            throw new \Parable\ORM\Exception("Can't run query without a database instance.");
        }
        return $this->getInstance()->query($query, \PDO::FETCH_ASSOC);
    }

    /**
     * Use an array to pass multiple config values at the same time. The values must correspond to setters
     * defined on this class. If not, an exception is thrown.
     *
     * @param array $config
     *
     * @return $this
     * @throws \Parable\ORM\Exception
     */
    public function setConfig(array $config)
    {
        foreach ($config as $type => $value) {
            $property = ucwords(str_replace('-', ' ', $type));
            $property = lcfirst(str_replace(' ', '', $property));

            $method = 'set' . ucfirst($property);

            if (method_exists($this, $method)) {
                $this->{$method}($value);
            } else {
                throw new \Parable\ORM\Exception(
                    "Tried to set non-existing property '{$property}' with value '{$value}' on " . get_class($this)
                );
            }
        }
        return $this;
    }

    /**
     * Prevent leaking sensitive information.
     */
    public function __debugInfo()
    {
    }
}
