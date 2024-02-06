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
        echo $this->lastSql;
        $result = $db->getRow($this->lastSql);
        dd(1, $result);
        if ($db->lasterror()) {
            throw new Exception($this->lastSql . PHP_EOL . $db->lasterror());
        }

        if ($result) {
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
}