<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Contracts;

use WMS\Xtent\Contracts\AbstractObjectData;

trait CanSaveDataByDatabaseTrait
{

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

    function fetch($id = null, $field = null): ?static
    {

        $primaryKey = $field ?? $this->getPrimaryKey();

        $rowId = $id ?? $this->{$primaryKey} ?? null;

        $db = getDbInstance();
        if (!$rowId) {
            return null;
        }
        $where = array_merge($this->defaultCondition(), [$primaryKey => $rowId]);
        $whereArr = [];

        foreach ($where as $k => $vl) {
            $whereArr[] = "{$k} = '{$vl}'";
        }
        $sqlWhere = implode(' AND ', $whereArr);

        $query = 'SELECT * FROM ' . getDbPrefix() . ltrim($this->getMainTable(), getDbPrefix()) . " WHERE {$sqlWhere}";

        $result = $db->getRow($query);
        if ($db->lasterror()) {
            throw new \Exception($query . PHP_EOL . $db->lasterror());
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

    public function save(array $data = []): bool
    {
        $this->addData($data);
        $primaryKey = $this->getPrimaryKey();
        $db = getDbInstance();

        if ($this->{$primaryKey}) {
            $values = [];
            foreach ($this->toArray() as $column => $value) {
                if (in_array($column, array_keys(AbstractObjectData::$casts[static::class] ?? []))) {
                    $values[] = "{$column} = '{$value}'";
                }

            }

            $result = $db->query($query = "UPDATE " . getDbPrefix() . ltrim($this->getMainTable(), getDbPrefix()) . " SET " . implode(',', $values) . " WHERE {$primaryKey} = '{$this->{$primaryKey}}'");
        } else {
            $columns = [];
            $values = [];
            foreach ($this->toArray() as $column => $value) {
                if ($column == $primaryKey) continue;
                if (in_array($column, array_keys(AbstractObjectData::$casts[static::class] ?? []))) {
                    $columns[] = $column;
                    $values[] = "'{$value}'";
                }
            }
            $table = getDbPrefix() . ltrim($this->getMainTable(), getDbPrefix());

            $result = $db->query($query = "INSERT INTO " . $table . " (" . implode(',', $columns) . ") VALUES (" . implode(',', $values) . ")");

            $this->addData([$primaryKey => $db->last_insert_id($table)]);
        }

        if ($db->lasterror()) {

            throw new \Exception($query . PHP_EOL . $db->lasterror());
        }

        return empty($db->lasterrno);
    }

}