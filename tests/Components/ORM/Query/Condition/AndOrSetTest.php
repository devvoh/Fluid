<?php

namespace Parable\Tests\Components\ORM\Query\Condition;

class AndOrSetTest extends \Parable\Tests\Components\ORM\Base
{
    /** @var \Parable\ORM\Query\Condition\AndSet */
    protected $andSet;

    /** @var \Parable\ORM\Query */
    protected $query;

    protected function setUp()
    {
        parent::setUp();

        $this->query = \Parable\ORM\Query::createInstance();
        $this->query->setTableName('user');
    }

    public function testCreateNewAndConditionSetWithArrayConditions()
    {
        $conditions = [
            ['id', '=', 1],
            ['active', 'in', [0, 1]],
        ];
        $andSet = new \Parable\ORM\Query\Condition\AndSet($this->query, $conditions);

        $this->assertSame("(`user`.`id` = '1' AND `user`.`active` IN ('0','1'))", $andSet->buildWithParentheses());
        $this->assertSame("`user`.`id` = '1' AND `user`.`active` IN ('0','1')", $andSet->buildWithoutParentheses());
    }

    public function testCreateNewAndConditionSetWithConditionObjects()
    {
        $conditions = [
            $this->createCondition('id', '=', 1),
            $this->createCondition('active', 'in', [0, 1]),
        ];

        $andSet = new \Parable\ORM\Query\Condition\AndSet($this->query, $conditions);

        $this->assertSame("(`user`.`id` = '1' AND `user`.`active` IN ('0','1'))", $andSet->buildWithParentheses());
        $this->assertSame("`user`.`id` = '1' AND `user`.`active` IN ('0','1')", $andSet->buildWithoutParentheses());
    }

    public function testCreateNewOrConditionSetWithArrayConditions()
    {
        $conditions = [
            ['id', '=', 1],
            ['active', 'in', [0, 1]],
        ];
        $andSet = new \Parable\ORM\Query\Condition\OrSet($this->query, $conditions);

        $this->assertSame("(`user`.`id` = '1' OR `user`.`active` IN ('0','1'))", $andSet->buildWithParentheses());
        $this->assertSame("`user`.`id` = '1' OR `user`.`active` IN ('0','1')", $andSet->buildWithoutParentheses());
    }

    public function testCreateNewOrConditionSetWithConditionObjects()
    {
        $conditions = [
            $this->createCondition('id', '=', 1),
            $this->createCondition('active', 'in', [0, 1]),
        ];

        $andSet = new \Parable\ORM\Query\Condition\OrSet($this->query, $conditions);

        $this->assertSame("(`user`.`id` = '1' OR `user`.`active` IN ('0','1'))", $andSet->buildWithParentheses());
        $this->assertSame("`user`.`id` = '1' OR `user`.`active` IN ('0','1')", $andSet->buildWithoutParentheses());
    }

    public function testNestedAndOrConditionSetsWithAndAsPrimarySet()
    {
        $conditions = [
            $this->createCondition('created_at', '<', '2017-01-15 12:00:00'),
            $this->createCondition('created_at', '>', '2017-01-01 12:00:00'),
        ];
        $orSet = new \Parable\ORM\Query\Condition\OrSet($this->query, $conditions);

        $conditionsAnd = [
            $this->createCondition('id', '=', 1),
            $this->createCondition('active', 'in', [0, 1]),
            $orSet,
        ];
        $andSet = new \Parable\ORM\Query\Condition\AndSet($this->query, $conditionsAnd);

        $this->assertSame(
            "(`user`.`id` = '1' AND `user`.`active` IN ('0','1') AND (`user`.`created_at` < '2017-01-15 12:00:00' OR `user`.`created_at` > '2017-01-01 12:00:00'))",
            $andSet->buildWithParentheses()
        );
    }

    public function testNestedAndOrConditionSetsWithOrAsPrimarySet()
    {
        $conditionsAnd = [
            $this->createCondition('id', '=', 1),
            $this->createCondition('active', 'in', [0, 1]),
        ];
        $andSet = new \Parable\ORM\Query\Condition\AndSet($this->query, $conditionsAnd);

        $conditions = [
            $this->createCondition('created_at', '<', '2017-01-15 12:00:00'),
            $this->createCondition('created_at', '>', '2017-01-01 12:00:00'),
            $andSet,
        ];
        $orSet = new \Parable\ORM\Query\Condition\OrSet($this->query, $conditions);

        $this->assertSame(
            "(`user`.`created_at` < '2017-01-15 12:00:00' OR `user`.`created_at` > '2017-01-01 12:00:00' OR (`user`.`id` = '1' AND `user`.`active` IN ('0','1')))",
            $orSet->buildWithParentheses()
        );
    }

    /**
     * @param string $key
     * @param string $comparator
     * @param mixed  $value
     * @return \Parable\ORM\Query\Condition
     */
    protected function createCondition($key, $comparator, $value)
    {
        $condition = new \Parable\ORM\Query\Condition();
        $condition->setQuery($this->query);

        $condition->setKey($key);
        $condition->setComparator($comparator);
        $condition->setValue($value);

        return $condition;
    }
}
