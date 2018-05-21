<?php

namespace Parable\Tests\Components\Framework;

class AuthenticationTest extends \Parable\Tests\Components\Framework\Base
{
    /** @var \Parable\Framework\Authentication */
    protected $authentication;

    /** @var \Parable\GetSet\Session */
    protected $session;

    /** @var \Parable\Tests\TestClasses\Model */
    protected $user;

    protected function setUp()
    {
        parent::setUp();

        $this->authentication = \Parable\DI\Container::get(\Parable\Framework\Authentication::class);
        $this->session        = \Parable\DI\Container::get(\Parable\GetSet\Session::class);

        $this->user = \Parable\DI\Container::create(\Parable\Tests\TestClasses\Model::class);
        $this->user->username = "test";
        $this->user->password = $this->authentication->generatePasswordHash('test');
        $this->user->email    = "test@test.dev";
        $this->user->save();
    }

    public function testInitialize()
    {
        // A freshly created authentication instance knows nothing
        $this->assertFalse($this->authentication->isAuthenticated());
        $this->assertFalse($this->authentication->initialize());
    }

    public function testSetGetUserClassName()
    {
        $this->authentication->setUserClassName(\Parable\Tests\TestClasses\Model::class);
        $this->assertSame(
            \Parable\Tests\TestClasses\Model::class,
            $this->authentication->getUserClassName()
        );

        // And passing an array shouldn't be accepted AND throw the correct exception
        $this->expectException(\Parable\Framework\Exception::class);
        $this->expectExceptionMessage("Invalid object provided, type Parable\Tests\TestClasses\Model required.");
        $this->authentication->setUser([]);
    }

    public function testSetGetUserIdProperty()
    {
        $this->authenticateUser();

        // Assert the default situation
        $this->assertSame(
            ["user_id" => $this->user->id],
            $this->authentication->getAuthenticationData()
        );

        $this->authentication->setUserIdProperty("other_id");
        $this->authentication->reset();
        $this->authenticateUser();

        // It'll be empty because we're looking for the wrong property at this point
        $this->assertEmpty($this->authentication->getAuthenticationData());

        $this->user->other_id = 1337;
        $this->authenticateUser();

        // The value now exists, so it should match
        $this->assertSame(
            ["user_id" => 1337],
            $this->authentication->getAuthenticationData()
        );
    }

    public function testSetUserClassNameThrowsExceptionIfClassDoesntExist()
    {
        $this->expectException(\Parable\Framework\Exception::class);
        $this->expectExceptionMessage("Class 'Yeah come on, I don't think so.' could not be instantiated.");

        $this->authentication->setUserClassName("Yeah come on, I don't think so.");
    }

    public function testSettingUserAndAuthenticatingWorksProperly()
    {
        $this->authentication->setUserClassName(\Parable\Tests\TestClasses\Model::class);
        $this->authentication->setUser($this->user);

        $this->assertFalse($this->authentication->authenticate('test-wrong', $this->user->password));
        $this->assertFalse($this->authentication->isAuthenticated());

        $this->assertTrue($this->authentication->authenticate('test', $this->user->password));
        $this->assertTrue($this->authentication->isAuthenticated());

        $this->assertSame($this->user, $this->authentication->getUser());

        $this->assertTrue($this->authentication->initialize());

        $this->assertSame(
            ["user_id" => $this->user->id],
            $this->authentication->getAuthenticationData()
        );
    }

    public function testInitializeExpectsCertainAuthenticationData()
    {
        $this->authenticateUser();
        $this->assertTrue($this->authentication->initialize());

        // Overwrite the auth data so 'something' went wrong, or it was implemented incorrectly.
        $this->session->set('auth', ['test' => 'no']);
        $this->assertFalse($this->authentication->initialize());
    }

    public function testInitializeExpectsExistingUser()
    {
        $this->authenticateUser();
        $this->assertTrue($this->authentication->initialize());

        // Overwrite the auth data so 'something' went wrong, or it was implemented incorrectly.
        $this->user->delete();
        $this->assertFalse($this->authentication->initialize());
    }

    public function testResetUserWorks()
    {
        $this->authenticateUser();

        $this->assertNotNull($this->authentication->getUser());
        $this->assertNotEmpty($this->authentication->getAuthenticationData());

        $this->authentication->resetUser();

        $this->assertNull($this->authentication->getUser());
        $this->assertEmpty($this->authentication->getAuthenticationData());
    }

    public function testResetRevokesAuthAndUnsetsUser()
    {
        $this->authenticateUser();

        $this->assertNotNull($this->authentication->getUser());
        $this->assertNotNull($this->session->get('auth'));
        $this->assertTrue($this->authentication->initialize());
        $this->assertNotEmpty($this->authentication->getAuthenticationData());

        $this->authentication->reset();

        $this->assertNull($this->authentication->getUser());
        $this->assertNull($this->session->get('auth'));
        $this->assertFalse($this->authentication->initialize());
        $this->assertEmpty($this->authentication->getAuthenticationData());
    }

    /**
     * Sets an authenticatable user on the Authentication class and authenticates 'm.
     */
    protected function authenticateUser()
    {
        $this->authentication->setUserClassName(\Parable\Tests\TestClasses\Model::class);
        $this->authentication->setUser($this->user);
        $this->authentication->authenticate('test', $this->user->password);
    }
}
