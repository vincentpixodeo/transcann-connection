<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Contracts;

use Exception;
use Generator;
use WMS\Xtent\Contracts\AbstractObjectData;

trait CanSaveDataByDatabaseTrait
{
    use HasSqlBuilder;

    protected ?string $lastSql = null;

    protected array $_original = [];


    /**
     * get Main Table
     * @return string
     */
    abstract public function getMainTable(): string;

    abstract public function getPrimaryKey(): string;

    public function id($value = null): string|int|null
    {
        if (!is_null($value)) {
            $this->{$this->getPrimaryKey()} = $value;
        }
        return $this->{$this->getPrimaryKey()};
    }

    protected function defaultCondition(): array
    {
        return [];
    }

    /**
     * @param array $fields
     * @param array $wheres
     * @return bool|resource
     * @throws Exception
     */
    static function update(array $fields, array $wheres)
    {
        $values = [];
        $sqlBuilder = (new static())->buildSelectSql();

        foreach ($fields as $column => $value) {
            if (in_array($column, array_keys(AbstractObjectData::$casts[static::class] ?? []))) {
                $values[$column] = $value;
            }
        }
        if (!$values) return false;

        $db = getDbInstance();
        $sqlBuilder->where($wheres);
        $query = $sqlBuilder->toUpdateSql($values);

        $result = $db->query($query);

        if ($db->lasterror()) {
            throw new Exception($query . PHP_EOL . $db->lasterror());
        }
        return $result;
    }

    /**
     * @param $id
     * @param $field
     * @param callable|null $queryBuilderCallback
     * @return static|null
     * @throws Exception
     */
    static function load($id = null, $field = null, callable $queryBuilderCallback = null): ?static
    {
        return (new static())->fetch($id, $field, $queryBuilderCallback);
    }

    /**
     * @param array $condition
     * @param callable|null $queryBuilderCallback
     * @return Generator
     * @throws Exception
     */
    static function get(array $condition = [], callable $queryBuilderCallback = null): Generator
    {
        return (new static())->list($condition, $queryBuilderCallback);
    }


    /**
     * @param array $condition
     * @param callable|null $queryBuilderCallback
     * @return Generator
     * @throws Exception
     */
    function list(array $condition = [], callable $queryBuilderCallback = null): Generator
    {
        $sqlBuilder = $this->buildSelectSql($condition, 'list');
        if ($queryBuilderCallback) {
            $queryBuilderCallback($sqlBuilder);
        }

        $db = getDbInstance();

        $results = $db->query($query = (string)$sqlBuilder);

        if ($db->lasterror()) {
            throw new Exception($query . PHP_EOL . $db->lasterror());
        }
        while ($row = $db->fetch_object($results)) {
            $instance = new static((array)$row);
            $instance->_original = (array)$row;
            yield $instance;
        }
    }

    /**
     * @param $id
     * @param $field
     * @param callable|null $queryBuilderCallback
     * @return $this|null
     * @throws Exception
     */

    function fetch($id = null, $field = null, callable $queryBuilderCallback = null): ?static
    {

        $primaryKey = $field ?? $this->getPrimaryKey();

        $rowId = $id ?? $this->{$primaryKey} ?? null;

        $db = getDbInstance();
        if (!$rowId) {
            return null;
        }

        $sqlBuilder = $this->buildSelectSql([$primaryKey => $rowId], 'fetch');

        if ($queryBuilderCallback) {
            $queryBuilderCallback($sqlBuilder);
        }

        $this->lastSql = (string)$sqlBuilder;

        $result = $db->getRow($this->lastSql);

        if ($db->lasterror()) {
            throw new Exception($this->lastSql . PHP_EOL . $db->lasterror());
        }

        if ($result) {
            $data = [];
            foreach ((array)$result as $key => $value) {
                if (!is_null($value)) $data[$key] = $value;
            }
            $this->_original = $data;
            return $this->addData($data);
        }

        return null;
    }

    /**
     * @param $id
     * @return bool
     * @throws Exception
     */
    function delete($id = null): bool
    {
        is_null($id) && $id = $this->id();
        if ($id) {
            $db = getDbInstance();
            $sqlBuilder = $this->buildSelectSql([], 'delete');
            $this->lastSql = $sqlBuilder->toDeleteSql($this->getPrimaryKey(), $id);
            $db->query($this->lastSql);
            if ($db->lasterror()) {

                throw new Exception($this->lastSql . PHP_EOL . $db->lasterror());
            }
            return true;
        }
        return false;
    }

    /**
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function save(array $data = []): bool
    {
        $this->addData($data);
        $primaryKey = $this->getPrimaryKey();
        $db = getDbInstance();

        $values = [];
        foreach ($this->toArray() as $column => $value) {
            if (in_array($column, array_keys(AbstractObjectData::$casts[static::class] ?? [])) && $value != ($this->_original[$column] ?? null)) {
                $values[$column] = $value;
            }
        }
        $hasUpdateDatabase = true;
        $result = 0;

        if (($id = $this->id()) && $values) {
            $sqlBuilder = $this->buildSelectSql([], 'save');
            $sqlBuilder->where($this->getPrimaryKey(), $id);
            $this->lastSql = $sqlBuilder->toUpdateSql($values);
            $result = $db->query($this->lastSql);
            $values = array_merge($this->_original, $values);
        } elseif ($values) {
            $sqlBuilder = $this->buildSelectSql([], 'insert');
            $this->lastSql = $sqlBuilder->toInsertSql($values);
            $result = $db->query($this->lastSql);
            $id = $db->last_insert_id($sqlBuilder->getTable());
            $values[$primaryKey] = $id;
            $this->addData([$primaryKey => $id]);
        } else {
            $hasUpdateDatabase = false;
        }

        if ($hasUpdateDatabase && $db->lasterror()) {
            throw new Exception($this->lastSql . PHP_EOL . $db->lasterror());
        }
        if ($hasUpdateDatabase) {
            $this->_original = $values;
        }
        return $hasUpdateDatabase;
    }

}