<?php

namespace Parable\ORM;

class Repository
{
    /** @var \Parable\ORM\Database */
    protected $database;

    /** @var \Parable\ORM\Model */
    protected $model;

    /** @var array */
    protected $orderBy = [];

    /** @var array */
    protected $limitOffset = [];

    /** @var bool */
    protected $onlyCount = false;

    /** @var bool */
    protected $returnOne = false;

    public function __construct(
        \Parable\ORM\Database $database
    ) {
        $this->database = $database;
    }

    /**
     * Generate a query set to use the current Model's table name & key.
     *
     * @return \Parable\ORM\Query
     */
    public function createQuery()
    {
        $query = \Parable\ORM\Query::createInstance();
        $query->setTableName($this->getModel()->getTableName());

        if ($this->onlyCount) {
            $query->select(['count(*)']);
        }
        if (!empty($this->orderBy)) {
            $query->orderBy($this->orderBy['key'], $this->orderBy['direction']);
        }
        if (!empty($this->limitOffset)) {
            $query->limitOffset($this->limitOffset['limit'], $this->limitOffset['offset']);
        }
        if ($this->returnOne) {
            $query->limitOffset(1);
        }

        return $query;
    }

    /**
     * Returns all rows for this model type.
     *
     * @return \Parable\ORM\Model[]|\Parable\ORM\Model
     */
    public function getAll()
    {
        $query = $this->createQuery();
        $result = $this->database->query($query);

        $entities = [];
        if ($result) {
            $result = $result->fetchAll(\PDO::FETCH_ASSOC);
            $entities = $this->handleResult($result);
        }
        if ($this->returnOne && is_array($entities)) {
            return current($entities);
        }
        return $entities;
    }

    /**
     * Returns a single model, based on $id.
     *
     * @param int $id
     *
     * @return null|\Parable\ORM\Model
     */
    public function getById($id)
    {
        $query = $this->createQuery();
        $query->where(
            $query->buildAndSet([$this->getModel()->getTableKey(), '=', $id])
        );
        $result = $this->database->query($query);

        $model = null;
        if ($result) {
            $result = $result->fetchAll(\PDO::FETCH_ASSOC);
            $entities = $this->handleResult($result);
            $model = current($entities);
        }
        return $model;
    }

    /**
     * Returns all rows matching specific condition parameters given.
     *
     * @param string     $key
     * @param string     $comparator
     * @param mixed|null $value
     *
     * @return \Parable\ORM\Model[]|\Parable\ORM\Model
     * @throws \Parable\ORM\Exception
     */
    public function getByCondition($key, $comparator, $value = null)
    {
        $query = $this->createQuery();
        $conditionSet = $query->buildAndSet([$key, $comparator, $value]);
        return $this->getByConditionSet($conditionSet);
    }

    /**
     * Returns all rows matching specific conditionSet passed.
     *
     * @param \Parable\ORM\Query\ConditionSet $conditionSet
     *
     * @return \Parable\ORM\Model[]|\Parable\ORM\Model
     */
    public function getByConditionSet(\Parable\ORM\Query\ConditionSet $conditionSet)
    {
        return $this->getByConditionSets([$conditionSet]);
    }

    /**
     * Returns all rows matching all conditions passed.
     *
     * @param array $conditionSets
     *
     * @return \Parable\ORM\Model[]|\Parable\ORM\Model
     */
    public function getByConditionSets(array $conditionSets)
    {
        $query = $this->createQuery();
        $query->whereMany($conditionSets);
        $result = $this->database->query($query);

        $entities = [];
        if ($result) {
            $result = $result->fetchAll(\PDO::FETCH_ASSOC);
            $entities = $this->handleResult($result);
        }
        if ($this->returnOne && is_array($entities)) {
            return current($entities);
        }
        return $entities;
    }

    /**
     * Allow multiple orders by $key in $direction.
     *
     * @param string $key
     * @param string $direction ASC by default
     *
     * @return $this
     */
    public function orderBy($key, $direction = \Parable\ORM\Query::ORDER_ASC)
    {
        $this->orderBy = ['key' => $key, 'direction' => $direction];
        return $this;
    }

    /**
     * Sets the limitOffset.
     *
     * @param int      $limit
     * @param null|int $offset
     *
     * @return $this
     */
    public function limitOffset($limit, $offset = null)
    {
        $this->limitOffset = ['limit' => $limit, 'offset' => $offset];
        return $this;
    }

    /**
     * Set onlyCount to true or false.
     *
     * @param bool $value
     *
     * @return $this
     */
    public function setOnlyCount($value = true)
    {
        $this->onlyCount = (bool)$value;
        return $this;
    }

    /**
     * Sets the repo to return only one (the first), the same as getById always does.
     *
     * @return $this
     */
    public function returnOne()
    {
        $this->returnOne = true;
        return $this;
    }

    /**
     * Sets the repo to return all values, always in an array (except for getById).
     *
     * @return $this
     */
    public function returnAll()
    {
        $this->returnOne = false;
        return $this;
    }

    /**
     * Returns a fresh clone of the stored Model, with no values set.
     *
     * @return null|\Parable\ORM\Model
     */
    public function createModel()
    {
        $clone = clone $this->getModel();
        return $clone->reset();
    }

    /**
     * Set a model on the repository. Reset it so there's no unwanted values stored on it..
     *
     * @param \Parable\ORM\Model $model
     *
     * @return $this
     */
    public function setModel(\Parable\ORM\Model $model)
    {
        $this->model = $model->reset();
        return $this;
    }

    /**
     * Return model.
     *
     * @return null|\Parable\ORM\Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Build and return an AND condition set.
     *
     * @param \Parable\ORM\Query\Condition[] $conditions
     *
     * @return \Parable\ORM\Query\Condition\AndSet
     */
    public function buildAndSet(array $conditions)
    {
        return $this->createQuery()->buildAndSet($conditions);
    }

    /**
     * Build and return an OR condition set.
     *
     * @param \Parable\ORM\Query\Condition[] $conditions
     *
     * @return \Parable\ORM\Query\Condition\OrSet
     */
    public function buildOrSet(array $conditions)
    {
        return $this->createQuery()->buildOrSet($conditions);
    }

    /**
     * Handle the result of one of the get functions. This attempts to create a new model
     * with the values returned properly set.
     *
     * @param array $result
     *
     * @return \Parable\ORM\Model[]|int
     */
    protected function handleResult(array $result)
    {
        if ($this->onlyCount && isset($result[0]) && is_array($result[0])) {
            return (int)current($result[0]);
        }

        $entities = [];
        foreach ($result as $row) {
            $model = clone $this->getModel();
            $model->populate($row);
            $entities[] = $model;
        }
        return $entities;
    }

    /**
     * Resets everything but the query and model class to their default values.
     */
    public function reset()
    {
        $this->orderBy = [];
        $this->limitOffset = [];
        $this->setOnlyCount(false);
        $this->returnAll();
    }

    /**
     * Create an instance of the repository class for given $modelName.
     *
     * @param string $modelName
     *
     * @return \Parable\ORM\Repository
     */
    public static function createForModelName($modelName)
    {
        if (!class_exists($modelName)) {
            throw new Exception("Model '{$modelName}' does not exist.");
        }
        return self::createForModel($modelName::create());
    }

    /**
     * Create an instance of the repository class for given $model.
     *
     * @param \Parable\ORM\Model $model
     *
     * @return Repository
     */
    public static function createForModel(\Parable\ORM\Model $model)
    {
        $repository = \Parable\DI\Container::create(static::class);
        $repository->setModel($model);
        return $repository;
    }
}
