<?php

namespace Parable\Tests\Components\ORM\Query;

class ConditionTest extends \Parable\Tests\Components\ORM\Base
{
    /** @var \Parable\ORM\Query\Condition */
    protected $condition;

    /** @var \Parable\ORM\Query */
    protected $query;

    protected function setUp()
    {
        parent::setUp();

        $this->condition = new \Parable\ORM\Query\Condition();

        $this->query = \Parable\ORM\Query::createInstance();
        $this->query->setTableName('user');

        $this->condition->setQuery($this->query);
    }

    public function testSetQuery()
    {
        // By default the TableName should be user, passed from Query
        $this->assertSame('user', $this->condition->getTableName());

        $this->condition->setQuery(\Parable\ORM\Query::createInstance());

        // New Query doesn't have a TableName
        $this->assertNull($this->condition->getTableName());
    }

    public function testSetAndGetTableNameAndJoinTableName()
    {
        $this->condition->setTableName('user');
        $this->condition->setJoinTableName('settings');

        $this->assertSame('user', $this->condition->getTableName());
        $this->assertSame('settings', $this->condition->getJoinTableName());
    }

    public function testConditionProperlyBuiltWithSimpleValues()
    {
        $this->condition->setKey('id');
        $this->condition->setComparator('=');
        $this->condition->setValue(1);

        $this->assertSame("`user`.`id` = '1'", $this->condition->build());
    }

    public function testConditionProperlyBuiltWithInNotInChecks()
    {
        $this->condition->setKey('id');
        $this->condition->setComparator('IN');
        $this->condition->setValue([1,2,3]);

        $this->assertSame("`user`.`id` IN ('1','2','3')", $this->condition->build());

        $this->condition->setKey('id');
        $this->condition->setComparator('NOT IN');
        $this->condition->setValue([1,2,3]);

        $this->assertSame("`user`.`id` NOT IN ('1','2','3')", $this->condition->build());
    }

    public function testConditionProperlyBuiltWithIsNullIsNotNullChecks()
    {
        $this->condition->setKey('id');
        $this->condition->setComparator('IS NULL');

        $this->assertSame("`user`.`id` IS NULL", $this->condition->build());

        $this->condition->setKey('id');
        $this->condition->setComparator('is NOT null');

        $this->assertSame("`user`.`id` IS NOT NULL", $this->condition->build());
    }

    public function testShouldQuoteValues()
    {
        $this->condition->setKey('id');
        $this->condition->setValue('test');

        $this->assertSame("`user`.`id` = 'test'", $this->condition->build());

        $this->condition->setShouldQuoteValues(false);

        $this->assertSame("`user`.`id` = test", $this->condition->build());
    }

    public function testShouldCompareFields()
    {
        $this->condition->setKey('id');
        $this->condition->setValue('setting_id');
        $this->condition->setShouldCompareFields(true);

        // By default it only knows about one table, so it sets the same for both
        $this->assertSame("`user`.`id` = `user`.`setting_id`", $this->condition->build());

        // Setting a joinTableName will set it as the FIRST table to join on, matched with key
        $this->condition->setJoinTableName('settings');
        $this->assertSame("`settings`.`id` = `user`.`setting_id`", $this->condition->build());
    }
}
