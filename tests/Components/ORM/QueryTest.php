<?php

namespace Parable\Tests\Components\ORM;

class QueryTest extends \Parable\Tests\Components\ORM\Base
{
    /** @var \Parable\ORM\Query */
    protected $query;

    protected function setUp()
    {
        parent::setUp();

        $this->query = \Parable\DI\Container::create(\Parable\ORM\Query::class);
        $this->query->setTableName('user');
    }

    public function testBasicQuery()
    {
        $this->assertSame("SELECT * FROM `user`;", (string)$this->query);
    }

    public function testQuoteAndQuoteIdentifier()
    {
        $this->assertSame("'test'", $this->query->quote('test'));
        $this->assertSame("`test`", $this->query->quoteIdentifier('test'));
    }

    public function testQuoteAndQuoteIdentifierWithoutInstance()
    {
        $query = new \Parable\ORM\Query(new \Parable\ORM\Database());
        $this->assertSame("'test'", $query->quote('test'));
        $this->assertSame("`test`", $query->quoteIdentifier('test'));
    }

    public function testSetAndGetAndGetQuotedTableName()
    {
        $this->query->setTableName('sometable');
        $this->assertSame('sometable', $this->query->getTableName());
        $this->assertSame('`sometable`', $this->query->getQuotedTableName());
    }

    public function testSetAndGetAction()
    {
        $this->query->setAction('insert');
        $this->assertSame("insert", $this->query->getAction());
    }

    public function testSetInvalidActionThrowsException()
    {
        $this->expectException(\Parable\ORM\Exception::class);
        $this->expectExceptionMessage("Invalid action set, only select, insert, update, delete are allowed.");
        $this->query->setAction('stuff');
    }

    public function testSelectMethod()
    {
        $this->query->select(['*']);
        $this->assertSame("SELECT * FROM `user`;", (string)$this->query);

        $this->query->select(['id', 'name']);
        $this->assertSame("SELECT `user`.`id`, `user`.`name` FROM `user`;", (string)$this->query);
    }

    public function testBuildAndOrSets()
    {
        $set = $this->query->buildAndSet([
            ['id', '=', 1],
        ]);

        $this->assertInstanceOf(\Parable\ORM\Query\Condition\AndSet::class, $set);
        $this->assertSame(\Parable\ORM\Query\ConditionSet::SET_AND, $set::TYPE);

        $set = $this->query->buildOrSet([
            ['id', '=', 1],
        ]);

        $this->assertInstanceOf(\Parable\ORM\Query\Condition\OrSet::class, $set);
        $this->assertSame(\Parable\ORM\Query\ConditionSet::SET_OR, $set::TYPE);
    }

    public function testSelectAndWhere()
    {
        $this->query->where($this->query->buildAndSet([
            ['id', '=', 1],
            ['active', 'is not null'],
        ]));

        $this->assertSame(
            "SELECT * FROM `user` WHERE (`user`.`id` = '1' AND `user`.`active` IS NOT NULL);",
            (string)$this->query
        );
    }

    public function testSelectAndWhereMany()
    {
        $this->query->whereMany([
            $this->query->buildAndSet([
                ['id', '=', 1],
                ['active', 'is not null'],
            ]),
            $this->query->buildAndSet([
                ['other_id', '=', 1],
                ['inactive', 'is null'],
            ]),
        ]);

        $this->assertSame(
            "SELECT * FROM `user` WHERE (`user`.`id` = '1' AND `user`.`active` IS NOT NULL) AND (`user`.`other_id` = '1' AND `user`.`inactive` IS NULL);",
            (string)$this->query
        );
    }

    public function testWhereCondition()
    {
        $this->query->whereCondition("id", "=", 1);
        $this->query->whereCondition("updated_at", "is null");

        $this->assertSame(
            "SELECT * FROM `user` WHERE (`user`.`id` = '1') AND (`user`.`updated_at` IS NULL);",
            (string)$this->query
        );
    }

    public function testSelectWhereValueTypes()
    {
        $this->query->where($this->query->buildAndSet([
            ['id', '=', 1],
            ['active', 'is not null'],
            ['active', 'is null'],
            ['active', 'in', ['option1', 'option2']],
            ['active', 'not in', ['option1', 'option2']],
        ]));

        $this->assertSame(
            "SELECT * FROM `user` WHERE (`user`.`id` = '1' AND `user`.`active` IS NOT NULL AND `user`.`active` IS NULL AND `user`.`active` IN ('option1','option2') AND `user`.`active` NOT IN ('option1','option2'));",
            (string)$this->query
        );
    }

    public function testSelectAndHaving()
    {
        $this->query->having($this->query->buildAndSet([
            ['id', '=', 1],
            ['active', 'is not null'],
        ]));

        $this->assertSame(
            "SELECT * FROM `user` HAVING (`user`.`id` = '1' AND `user`.`active` IS NOT NULL);",
            (string)$this->query
        );
    }

    public function testSelectAndHavingMany()
    {
        $this->query->havingMany([
            $this->query->buildAndSet([
                ['id', '=', 1],
                ['active', 'is not null'],
            ]),
            $this->query->buildAndSet([
                ['other_id', '=', 1],
                ['inactive', 'is null'],
            ]),
        ]);

        $this->assertSame(
            "SELECT * FROM `user` HAVING (`user`.`id` = '1' AND `user`.`active` IS NOT NULL) AND (`user`.`other_id` = '1' AND `user`.`inactive` IS NULL);",
            (string)$this->query
        );
    }


    public function testInnerJoin()
    {
        $this->query->innerJoin('settings', 'user_id', '=', 'id');
        $this->assertSame(
            "SELECT * FROM `user` INNER JOIN `settings` ON `settings`.`user_id` = `user`.`id`;",
            (string)$this->query
        );
    }

    public function testFullJoin()
    {
        $this->query->fullJoin('settings', 'user_id', '=', 'id');
        $this->assertSame(
            "SELECT * FROM `user` FULL JOIN `settings` ON `settings`.`user_id` = `user`.`id`;",
            (string)$this->query
        );
    }

    public function testLeftJoin()
    {
        $this->query->leftJoin('settings', 'user_id', '=', 'id');
        $this->assertSame(
            "SELECT * FROM `user` LEFT JOIN `settings` ON `settings`.`user_id` = `user`.`id`;",
            (string)$this->query
        );
    }

    public function testRightJoin()
    {
        $this->query->rightJoin('settings', 'user_id', '=', 'id');
        $this->assertSame(
            "SELECT * FROM `user` RIGHT JOIN `settings` ON `settings`.`user_id` = `user`.`id`;",
            (string)$this->query
        );
    }

    public function testInsert()
    {
        $this->query->setAction('insert');
        $this->query->addValue('name', 'test');
        $this->query->addValue('active', 1);
        $this->query->addValue('thing', null);

        $this->assertSame(
            "INSERT INTO `user` (`name`, `active`, `thing`) VALUES ('test', '1', NULL);",
            (string)$this->query
        );
    }

    public function testInsertWithAddValues()
    {
        $this->query->setAction('insert');
        $this->query->addValues([
            'name'   => 'test',
            'active' => 1,
            'thing'  => null,
        ]);

        $this->assertSame(
            "INSERT INTO `user` (`name`, `active`, `thing`) VALUES ('test', '1', NULL);",
            (string)$this->query
        );
    }

    public function testUpdate()
    {
        $this->query->setAction('update');

        $this->query->addValue('name', 'test');
        $this->query->addValue('active', 1);
        $this->query->addValue('thing', null);

        $this->query->where($this->query->buildAndSet([
            ["id", "=", 3],
        ]));

        $this->assertSame(
            "UPDATE `user` SET `name` = 'test', `active` = '1', `thing` = NULL WHERE (`user`.`id` = '3');",
            (string)$this->query
        );
    }

    public function testSelectGivesEmptyStringOnNoSelect()
    {
        $this->query->setAction('select');
        $this->query->select([]);
        $this->assertEmpty((string)$this->query);
    }

    public function testInsertGivesEmptyStringOnNoValues()
    {
        $this->query->setAction('insert');
        $this->assertEmpty((string)$this->query);
    }

    public function testUpdateGivesEmptyStringOnNoValues()
    {
        $this->query->setAction('update');
        $this->assertEmpty((string)$this->query);
    }

    public function testDeleteGivesEmptyStringOnNoWheres()
    {
        $this->query->setAction('delete');
        $this->assertEmpty((string)$this->query);
    }

    /**
     * @dataProvider dpOrderBys
     *
     * @param $key
     * @param $direction
     */
    public function testOrderBy($key, $direction, $expected)
    {
        $this->query->orderBy($key, $direction);
        $this->assertSame($expected, (string)$this->query);
    }

    public function dpOrderBys()
    {
        return [
            ['id', \Parable\ORM\Query::ORDER_ASC, "SELECT * FROM `user` ORDER BY `user`.`id` ASC;"],
            ['id', \Parable\ORM\Query::ORDER_DESC, "SELECT * FROM `user` ORDER BY `user`.`id` DESC;"],
        ];
    }

    public function testMultipleOrderBys()
    {
        $this->query->orderBy("id", \Parable\ORM\Query::ORDER_DESC);
        $this->query->orderBy("name", \Parable\ORM\Query::ORDER_ASC);
        $this->assertSame(
            "SELECT * FROM `user` ORDER BY `user`.`id` DESC, `user`.`name` ASC;",
            (string)$this->query
        );
    }

    public function testMultipleOrderBysFromDifferentTables()
    {
        $this->query->orderBy("id", \Parable\ORM\Query::ORDER_DESC);
        $this->query->orderBy("name", \Parable\ORM\Query::ORDER_ASC, 'settings');
        $this->assertSame(
            "SELECT * FROM `user` ORDER BY `user`.`id` DESC, `settings`.`name` ASC;",
            (string)$this->query
        );
    }

    public function testGroupBy()
    {
        $this->query->groupBy('name');
        $this->assertSame("SELECT * FROM `user` GROUP BY `user`.`name`;", (string)$this->query);
    }

    public function testMultipleGroupBys()
    {
        $this->query->groupBy('name');
        $this->query->groupBy('id');
        $this->assertSame("SELECT * FROM `user` GROUP BY `user`.`name`, `user`.`id`;", (string)$this->query);
    }

    public function testMultipleGroupBysFromDifferentTables()
    {
        $this->query->groupBy('name');
        $this->query->groupBy('id', 'settings');
        $this->assertSame("SELECT * FROM `user` GROUP BY `user`.`name`, `settings`.`id`;", (string)$this->query);
    }

    public function testLimitOffset()
    {
        $this->query->limitOffset(15);
        $this->assertSame("SELECT * FROM `user` LIMIT 15;", (string)$this->query);

        $this->query->limitOffset(15, 10);
        $this->assertSame("SELECT * FROM `user` LIMIT 10,15;", (string)$this->query);
    }

    public function testCreateInstance()
    {
        $instance = \Parable\ORM\Query::createInstance();
        $this->assertInstanceOf(\Parable\ORM\Query::class, $instance);
    }

    public function testRidiculouslyComplexQuery()
    {
        $this->query->setTableName('complex');

        $this->query->where($this->query->buildAndSet([
            ['id', '=', 1],
            ['name', 'like', "%stuff"],
            ['active', 'is not null'],
            $this->query->buildOrSet([
                ['id', '!=', 1],
                ['active', 'is null'],
                ['created_at', 'in', ['option1', 'option2']],
            ])
        ]));

        $this->query->having($this->query->buildAndSet([
            ['id', '!=', 1],
            ['active', 'is null'],
        ]));

        $this->query->leftJoin('settings', 'user_id', '=', 'id');

        $this->query->limitOffset(0, 5);

        $this->query->orderBy("id", \Parable\ORM\Query::ORDER_DESC);
        $this->query->orderBy("name", \Parable\ORM\Query::ORDER_ASC, 'settings');

        $this->query->groupBy('name');
        $this->query->groupBy('id', 'settings');

        // Understandably, this query is also ridiculously long
        $this->assertSame(
            "SELECT * FROM `complex` LEFT JOIN `settings` ON `settings`.`user_id` = `complex`.`id` WHERE (`complex`.`id` = '1' AND `complex`.`name` LIKE '%stuff' AND `complex`.`active` IS NOT NULL AND (`complex`.`id` != '1' OR `complex`.`active` IS NULL OR `complex`.`created_at` IN ('option1','option2'))) GROUP BY `complex`.`name`, `settings`.`id` HAVING (`complex`.`id` != '1' AND `complex`.`active` IS NULL) ORDER BY `complex`.`id` DESC, `settings`.`name` ASC LIMIT 5;",
            (string)$this->query
        );
    }
}
