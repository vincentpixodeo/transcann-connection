<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use Exception;
use Generator;
use WMS\Xtent\Contracts\AbstractObjectData;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Database\Builder\QueryBuilder;

class StaticModel extends AbstractObjectData implements ObjectDataInterface
{
    protected ?string $lastSql = null;
    protected array $_original = [];

    public function __construct(protected string $table, protected string $primaryKey = 'rowid')
    {
        parent::__construct();
    }

    public function getMainTable(): string
    {
        return $this->table;
    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public function id($value = null): string|int|null
    {
        if (!is_null($value)) {
            $this->{$this->getPrimaryKey()} = $value;
        }
        return $this->{$this->getPrimaryKey()};
    }

    /**
     * @param array $condition
     * @param callable|null $queryBuilderCallback
     * @return Generator
     * @throws Exception
     */
    function list(array $condition = [], callable $queryBuilderCallback = null): Generator
    {
        $sqlBuilder = $this->buildSelectSql($condition);
        if ($queryBuilderCallback) {
            $queryBuilderCallback($sqlBuilder);
        }

        $db = getDbInstance();

        $results = $db->query($query = (string)$sqlBuilder);

        if ($db->lasterror()) {
            throw new Exception($query . PHP_EOL . $db->lasterror());
        }
        while ($row = $db->fetch_object($results)) {
            $instance = clone $this;
            $instance->_original = (array)$row;
            $instance->setData((array)$row);
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

        $sqlBuilder = $this->buildSelectSql([$primaryKey => $rowId]);

        if ($queryBuilderCallback) {
            $queryBuilderCallback($sqlBuilder);
        }

        $this->lastSql = (string)$sqlBuilder;

        $result = $db->getRow($this->lastSql);

        if ($db->lasterror()) {
            throw new Exception($this->lastSql . PHP_EOL . $db->lasterror());
        }

        if ($result) {
            $this->_original = (array)$result;
            return $this->addData((array)$result);
        }
        $this->_data = [];

        return null;
    }


    protected function buildSelectSql(array $where = []): QueryBuilder
    {
        $table = getDbPrefix() . ltrim($this->getMainTable(), getDbPrefix());
        $sqlBuilder = new QueryBuilder();
        $sqlBuilder->from($table);

        foreach ($where as $k => $vl) {
            if (is_array($vl)) {
                $sqlBuilder->where(...$vl);
            } elseif (is_numeric($k)) {
                $sqlBuilder->where($vl);
            } else {
                $sqlBuilder->where("{$k}", $vl);
            }
        }
        return $sqlBuilder;
    }

    function save(array $data = [])
    {
        $this->addData($data);

        $primaryKey = $this->getPrimaryKey();
        $db = getDbInstance();

        $values = [];
        foreach ($this->toArray() as $column => $value) {
            $values[$column] = $value;
        }
        $hasUpdateDatabase = true;
        $result = 0;
        if (($id = $this->id()) && $values) {
            $sqlBuilder = $this->buildSelectSql();
            $sqlBuilder->where($this->getPrimaryKey(), $id);
            $this->lastSql = $sqlBuilder->toUpdateSql($values);
            $result = $db->query($this->lastSql);
            $values = array_merge($this->_original, $values);
        } elseif ($values) {
            $sqlBuilder = $this->buildSelectSql();
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