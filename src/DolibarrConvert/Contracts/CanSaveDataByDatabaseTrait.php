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

    static function load($id = null, $field = null): ?static
    {
        return (new static())->fetch($id, $field);
    }

    /**
     * @param array $condition
     * @param int|null $limit
     * @param int|null $offset
     * @return Generator{static}
     * @throws Exception
     */
    static function get(array $condition = [], int $limit = null, int $offset = null): Generator
    {
        return (new static())->list($condition, $limit, $offset);
    }


    /**
     * @param array $condition
     * @return Generator{static}
     * @throws Exception
     */
    function list(array $condition = [], int $limit = null, int $offset = null): Generator
    {
        $sqlBuilder = $this->buildSelectSql($condition, 'list');
        if ($limit) {
            $sqlBuilder->limit($limit, $offset);
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
     * fetch one item by id
     * @param $id
     * @param $field
     * @return $this|null
     * @throws Exception
     */

    function fetch($id = null, $field = null): ?static
    {

        $primaryKey = $field ?? $this->getPrimaryKey();

        $rowId = $id ?? $this->{$primaryKey} ?? null;

        $db = getDbInstance();
        if (!$rowId) {
            return null;
        }

        $sqlBuilder = $this->buildSelectSql([$primaryKey => $rowId], 'fetch');

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
            return $this->addData(($data));
        }

        return null;
    }

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

    public function save(array $data = []): bool
    {
        $this->addData($data);
        $primaryKey = $this->getPrimaryKey();
        $db = getDbInstance();

        $values = [];
        foreach ($this->toArray() as $column => $value) {
            if (in_array($column, array_keys(AbstractObjectData::$casts[static::class] ?? []))) {
                $values[$column] = $value;
            }

        }

        if ($id = $this->{$primaryKey}) {
            $sqlBuilder = $this->buildSelectSql([], 'save');
            $sqlBuilder->where($this->getPrimaryKey(), $id);

            $result = $db->query($query = $sqlBuilder->toUpdateSql($values));
        } else {
            $sqlBuilder = $this->buildSelectSql([], 'insert');
            $result = $db->query($query = $sqlBuilder->toInsertSql($values));

            $this->addData([$primaryKey => $db->last_insert_id($sqlBuilder->getTable())]);
        }

        if ($db->lasterror()) {

            throw new Exception($query . PHP_EOL . $db->lasterror());
        }

        return empty($db->lasterrno);
    }

}