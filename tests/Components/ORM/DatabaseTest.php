<?php

namespace Parable\Tests\Components\ORM;

class DatabaseTest extends \Parable\Tests\Components\ORM\Base
{
    /** @var \Parable\ORM\Database */
    protected $database;

    /** @var \Parable\Filesystem\Path */
    protected $path;

    public function testDatabaseDirectQuery()
    {
        $user = current($this->database->query('select * from user')->fetchAll());
        $this->assertSame(
            [
                "id"         => '1',
                "username"   => "parable",
                "password"   => "plaintextpasswordsarebad",
                "email"      => "parable@test.dev",
                "created_at" => "2016-01-01 10:00:00",
                "updated_at" => null,
            ],
            $user
        );
    }

    public function testGetType()
    {
        $this->assertSame(\Parable\ORM\Database::TYPE_SQLITE, $this->database->getType());
    }

    public function testGetLocation()
    {
        $this->assertSame(\Parable\ORM\Database::LOCATION_SQLITE_MEMORY, $this->database->getLocation());
    }

    public function testSetAndGetUsername()
    {
        $this->database->setUsername('username');
        $this->assertSame('username', $this->database->getUsername());
    }

    public function testSetAndGetPassword()
    {
        $this->database->setPassword('password');
        $this->assertSame('password', $this->database->getPassword());
    }

    public function testSetAndGetDatabase()
    {
        $this->database->setDatabase('db');
        $this->assertSame('db', $this->database->getDatabase());
    }

    public function testSetAndGetErrorMode()
    {
        $this->assertSame(\PDO::ERRMODE_SILENT, $this->database->getErrorMode());

        $this->database->setErrorMode(\PDO::ERRMODE_EXCEPTION);
        $this->assertSame(\PDO::ERRMODE_EXCEPTION, $this->database->getErrorMode());
    }

    public function testSetAndGetCharset()
    {
        $this->assertSame(null, $this->database->getCharset());

        $this->database->setCharset("utf-8");
        $this->assertSame("utf-8", $this->database->getCharset());
    }

    public function testGetInstanceReturnsWorkingPDOInstance()
    {
        $this->assertInstanceOf(\Parable\ORM\Database\PDOSQLite::class, $this->database->getInstance());
    }

    public function testQuoteIdentifierUsesBackwardTicksRegardlessOfInstance()
    {
        $this->assertSame("`test`", $this->database->quoteIdentifier('test'));

        $database = \Parable\DI\Container::create(\Parable\ORM\Database::class);
        $this->assertSame("`test`", $database->quoteIdentifier('test'));
    }

    public function testDatabaseQuotesWithInstance()
    {
        $this->assertSame("'test'", $this->database->quote('test'));
    }

    public function testThrowsExceptionWhenQuotingValueWithoutInstance()
    {
        /** @var \Parable\ORM\Database $database */
        $database = \Parable\DI\Container::create(\Parable\ORM\Database::class);
        $this->assertNull($database->getInstance());

        $this->expectException(\Parable\ORM\Exception::class);
        $this->expectExceptionMessage("Can't quote value without a database instance.");
        $database->quote("test");
    }

    public function testThrowsExceptionWhenRunningQueryWithoutInstance()
    {
        /** @var \Parable\ORM\Database $database */
        $database = \Parable\DI\Container::create(\Parable\ORM\Database::class);
        $this->assertNull($database->getInstance());

        $this->expectException(\Parable\ORM\Exception::class);
        $this->expectExceptionMessage("Can't run query without a database instance.");
        $database->query("select * from `user`");
    }

    public function testSetConfig()
    {
        $this->database->setConfig(['username' => 'test']);
        $this->assertSame('test', $this->database->getUsername());
    }

    public function testSetConfigThrowsExceptionOnNonExistingKeys()
    {
        $this->expectException(\Parable\ORM\Exception::class);
        $this->expectExceptionMessage("Tried to set non-existing config value 'stuff' on Parable\ORM\Database");

        $this->database->setConfig(['stuff' => 'yay']);
    }

    public function testDebugInfoReturnsNothing()
    {
        $debugInfo = print_r($this->database, true);
        $debugInfo = str_replace(PHP_EOL, "", $debugInfo);

        $this->assertSame("Parable\ORM\Database Object()", $debugInfo);
    }

    public function testGetInstanceWithMySQL()
    {
        /** @var \Parable\ORM\Database $database */
        $database = $this->createPartialMock(\Parable\ORM\Database::class, ['createPDOMySQL']);

        $database
            ->method('createPDOMySQL')
            ->withAnyParameters()
            ->willReturn(new \Parable\Tests\TestClasses\FakePDOMySQL());

        // Make sure there's no instance
        $this->assertNull($database->getInstance());

        $database->setConfig([
            'type' => \Parable\ORM\Database::TYPE_MYSQL,
            'location'  => 'localhost',
            'username'  => 'username',
            'password'  => 'password',
            'database'  => 'database',
        ]);

        $this->assertInstanceOf(\Parable\ORM\Database\PDOMySQL::class, $database->getInstance());
    }

    public function testGetInstanceWithMySQLReturnsNullWithoutUsername()
    {
        $database = new \Parable\ORM\Database();
        $database->setConfig([
            'type' => \Parable\ORM\Database::TYPE_MYSQL,
            'location' => 'localhost',
            'password' => 'password',
            'database' => 'database',
        ]);
        $this->assertNull($database->getInstance());
    }

    public function testGetInstanceWithMySQLReturnsNullWithoutPassword()
    {
        $database = new \Parable\ORM\Database();
        $database->setConfig([
            'type' => \Parable\ORM\Database::TYPE_MYSQL,
            'location' => 'localhost',
            'username' => 'username',
            'database' => 'database',
        ]);
        $this->assertNull($database->getInstance());
    }

    public function testGetInstanceWithMySQLReturnsNullWithoutDatabase()
    {
        $database = new \Parable\ORM\Database();
        $database->setConfig([
            'type' => \Parable\ORM\Database::TYPE_MYSQL,
            'location' => 'localhost',
            'username' => 'username',
            'password' => 'password',
        ]);
        $this->assertNull($database->getInstance());
    }

    public function testGetInstanceThrowsExceptionOnInvalidType()
    {
        $this->expectException(\Parable\ORM\Exception::class);
        $this->expectExceptionMessage("Database type was invalid: CustardSQL");

        $database = new \Parable\ORM\Database();

        // Make sure there's no instance
        $this->assertNull($database->getInstance());

        $database->setConfig([
            'type'     => "CustardSQL",
            'location' => 'localhost',
            'username' => 'username',
            'password' => 'password',
            'database' => 'database',
        ]);
        $database->getInstance();
    }
}
