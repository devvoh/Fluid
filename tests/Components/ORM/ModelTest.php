<?php

namespace Parable\Tests\Components\ORM;

use \Parable\Tests\TestClasses\Model;

class ModelTest extends \Parable\Tests\Components\ORM\Base
{
    /** @var Model */
    protected $model;

    protected function setUp()
    {
        parent::setUp();

        $this->model = new Model($this->database);
    }

    public function testCreate()
    {
        $model = Model::create();

        // Two different instances are not the same.
        $this->assertNotSame($model, $this->model);

        // But they _are_ equal.
        $this->assertEquals($model, $this->model);
    }

    public function testModelCreateQuery()
    {
        $query = $this->model->createQuery();

        $this->assertInstanceOf(\Parable\ORM\Query::class, $query);
        $this->assertSame($this->model->getTableName(), $query->getTableName());
    }

    public function testSaveNew()
    {
        $now = (new \DateTime())->format('Y-m-d H:i:s');

        $this->model->username   = 'new user';
        $this->model->password   = 'password';
        $this->model->email      = 'email@test.dev';
        $this->model->created_at = $now;

        $this->model->save();

        $users = $this->database->query($this->model->createQuery())->fetchAll();
        $this->assertCount(4, $users);

        $this->assertSame('new user', $users[3]['username']);
    }

    public function testPopulateAndSaveExisting()
    {
        $userArray = current($this->database->query($this->model->createQuery())->fetchAll());
        $this->model->populate($userArray);

        $this->assertSame('parable', $this->model->username);

        $this->model->username = 'well this is different';
        $this->assertSame('well this is different', $this->model->username);

        $this->model->save();

        $userArray = current($this->database->query($this->model->createQuery())->fetchAll());
        $this->model->populate($userArray);
        $this->assertSame('well this is different', $this->model->username);
    }

    public function testDelete()
    {
        $result = $this->database->query($this->model->createQuery())->fetchAll();
        $this->assertCount(3, $result);

        foreach ($result as $user) {
            $this->model->id = $user['id'];
            $this->model->delete();
        }

        $result = $this->database->query($this->model->createQuery())->fetchAll();
        $this->assertEmpty($result);
    }

    public function testModelToArray()
    {
        $this->model->username  = 'testuser';
        $this->model->password  = 'password';
        $this->model->email     = \Parable\ORM\Database::NULL_VALUE;
        $this->model->created_at = null;

        $this->assertSame(
            [
                'username'   => 'testuser',
                'password'   => 'password',
                'email'      => null,
                'created_at' => null,
                'updated_at' => null,
                'id'         => null,
            ],
            $this->model->toArray()
        );
    }

    public function testModelToArrayWithoutEmptyValues()
    {
        $this->model->username  = 'testuser';
        $this->model->password  = 'password';
        // NULL_VALUE will keep it in the array but set it concretely to null, both on the model's array output
        // and the database queries run with it
        $this->model->email     = \Parable\ORM\Database::NULL_VALUE;
        // Just null keeps it from doing anything, since empty values (other than int 0) are ignored. This prevents
        // queries from attempting to write NULL for every value that's not specifically set. This allows for
        // minimally-populated models to be saved.
        $this->model->created_at = null;

        $modelArray = $this->model->toArrayWithoutEmptyValues();

        $this->assertSame(
            [
                'username'   => 'testuser',
                'password'   => 'password',
                'email'      => null,
            ],
            $modelArray
        );
        $this->assertArrayNotHasKey('created_at', $modelArray);
    }

    public function testModelToMappedArray()
    {
        $this->model->username = 'testuser';
        $this->model->password = 'password';

        $this->model->setMapper([
            'username' => 'user',
            'password' => 'pass',
        ]);

        $modelArray = $this->model->toArray();

        $this->assertSame('testuser', $modelArray['user']);
        $this->assertSame('password', $modelArray['pass']);
    }

    public function testModelExportToArray()
    {
        $this->model->username = 'testuser';
        $this->model->password = 'password';
        $this->model->created_at = null;

        $modelArray = $this->model->exportToArray();

        $this->assertSame(['username', 'email'], $this->model->getExportable());

        $this->assertSame('testuser', $modelArray['username']);
        $this->assertArrayNotHasKey('password', $modelArray);
        $this->assertArrayNotHasKey('created_at', $modelArray);
    }

    public function testModelExportToArrayWithoutEmptyValues()
    {
        $this->model->username   = 'testuser';
        $this->model->password   = 'password';
        $this->model->created_at = null;

        $modelArray = $this->model->exportToArrayWithoutEmptyValues();

        $this->assertSame(['username', 'email'], $this->model->getExportable());

        $this->assertSame('testuser', $modelArray['username']);
        $this->assertArrayNotHasKey('password', $modelArray);
    }

    public function testSetTableKey()
    {
        $this->model->setTableKey('key');
        $this->assertSame('key', $this->model->getTableKey());
    }

    public function testSetTableName()
    {
        $this->model->setTableName('tableName');
        $this->assertSame('tableName', $this->model->getTableName());
    }
}
