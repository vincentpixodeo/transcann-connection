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

    protected array $_original = [];


    /**
     * get Main Table
     * @return string
     */
    abstract public function getMainTable(): string;

    abstract public function getPrimaryKey(): string;

    public function id(): string|int|null
    {
        return $this->{$this->getPrimaryKey()};
    }

    protected function defaultCondition(): array
    {
        return [];
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
     * @param int|null $limit
     * @param int|null $offset
     * @return Generator{static}
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
            yield new static((array)$row);
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

        $query = (string)$sqlBuilder;

        $result = $db->getRow($query);

        if ($db->lasterror()) {
            throw new Exception($query . PHP_EOL . $db->lasterror());
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
            $db->query($query = $sqlBuilder->toDeleteSql($this->getPrimaryKey(), $id));
            if ($db->lasterror()) {

                throw new Exception($query . PHP_EOL . $db->lasterror());
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
            $result = $db->query($query = $sqlBuilder->toUpdateSql($values));
            $values = array_merge($this->_original, $values);
        } elseif ($values) {
            $sqlBuilder = $this->buildSelectSql([], 'insert');
            $result = $db->query($query = $sqlBuilder->toInsertSql($values));
            $id = $db->last_insert_id($sqlBuilder->getTable());
            $values[$primaryKey] = $id;
            $this->addData([$primaryKey => $id]);
        } else {
            $hasUpdateDatabase = false;
        }

        if ($hasUpdateDatabase && $db->lasterror()) {
            throw new Exception($query . PHP_EOL . $db->lasterror());
        }
        if ($hasUpdateDatabase) {
            $this->_original = $values;
        }
        return $hasUpdateDatabase;
    }

}