<?php

namespace Parable\Framework;

class Authentication
{
    /** @var string */
    protected $userClassName = '\Model\User';

    /** @var object|null */
    protected $user;

    /** @var \Parable\Framework\Toolkit */
    protected $toolkit;

    /** @var \Parable\GetSet\Session */
    protected $session;

    /** @var bool */
    protected $authenticated = false;

    /** @var array */
    protected $authenticationData = [];

    public function __construct(
        \Parable\Framework\Toolkit $toolkit,
        \Parable\GetSet\Session $session
    ) {
        $this->toolkit = $toolkit;
        $this->session = $session;
    }

    /**
     * Initialize the authentication, picking up on session data if possible.
     *
     * @return bool
     */
    public function initialize()
    {
        if ($this->checkAuthentication()) {
            $data = $this->getAuthenticationData();
            if (!isset($data['user_id'])) {
                return false;
            }

            $user = $this->toolkit->getRepository($this->userClassName)->getById($data['user_id']);
            if (!$user) {
                $this->setAuthenticated(false);
                $this->setAuthenticationData([]);
                return false;
            }
            $this->setUser($user);
            return true;
        }
        return false;
    }

    /**
     * Generate a password hash from the provided $password.
     *
     * @param $password
     *
     * @return bool|string
     */
    public function generatePasswordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Checks whether there's an auth session active at this point.
     *
     * @return bool
     */
    protected function checkAuthentication()
    {
        $authSession = $this->session->get('auth');
        if ($authSession) {
            if (isset($authSession['authenticated'])) {
                $this->setAuthenticated($authSession['authenticated']);
            } else {
                $this->setAuthenticated(false);
            }

            if (isset($authSession['data'])) {
                $this->setAuthenticationData($authSession['data']);
            } else {
                $this->setAuthenticationData([]);
            }

            return true;
        }
        return false;
    }

    /**
     * Sets whether there's an authenticated user or not.
     *
     * @param bool $value
     *
     * @return $this
     */
    public function setAuthenticated($value = true)
    {
        $this->authenticated = (bool)$value;
        return $this;
    }

    /**
     * Checks whether there's an authenticated user or not.
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * Set the data for the user currently authenticated.
     *
     * @param array $data
     *
     * @return $this
     */
    public function setAuthenticationData(array $data)
    {
        $this->authenticationData = $data;
        return $this;
    }

    /**
     * Return the authentication data.
     *
     * @return array
     */
    public function getAuthenticationData()
    {
        return $this->authenticationData;
    }

    /**
     * Set the class name to use for the user.
     *
     * @param string $className
     *
     * @return $this
     * @throws \Parable\Framework\Exception
     */
    public function setUserClassName($className)
    {
        try {
            \Parable\DI\Container::create($className);
        } catch (\Exception $e) {
            throw new \Parable\Framework\Exception("Class '{$className}' could not be instantiated.");
        }

        $this->userClassName = $className;
        return $this;
    }

    /**
     * Return the class name for the user.
     *
     * @return string
     */
    public function getUserClassName()
    {
        return $this->userClassName;
    }

    /**
     * Set the user and check whether it's of the right type.
     *
     * @param $user
     *
     * @return $this
     * @throws \Parable\Framework\Exception
     */
    public function setUser($user)
    {
        if (!($user instanceof $this->userClassName)) {
            throw new \Parable\Framework\Exception("Invalid object provided, type {$this->userClassName} required.");
        }
        $this->user = $user;
        return $this;
    }

    /**
     * Return the user entity, if it exists.
     *
     * @return null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Check whether the provided password matches the password hash.
     *
     * @param string $passwordProvided
     * @param string $passwordHash
     *
     * @return bool
     */
    public function authenticate($passwordProvided, $passwordHash)
    {
        if (password_verify($passwordProvided, $passwordHash)) {
            $this->setAuthenticated(true);

            if ($this->getUser()) {
                $this->setAuthenticationData(['user_id' => $this->user->id]);
            }
            $this->session->set('auth', [
                'authenticated' => true,
                'data' => $this->authenticationData,
            ]);
        } else {
            $this->revokeAuthentication();
        }
        return $this->isAuthenticated();
    }

    /**
     * Revoke an existing authentication.
     *
     * @return $this
     */
    public function revokeAuthentication()
    {
        $this->setAuthenticated(false);
        $this->session->remove('auth');

        return $this;
    }
}
