<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */


declare(strict_types=1);

namespace WMS\Xtent\Database\Builder;

class QueryBuilder
{
    private ?string $table = null;
    private ?string $alias = null;
    private array $columns = [];
    private array $joins = [];
    private array $wheres = [];
    private ?string $sql = null;

    private ?string $where = null;
    private bool $isOrWhere = false;
    private ?string $limit = null;
    private ?string $orderBy;


    private function resetQuery()
    {
        $this->table = null;
        $this->alias = null;
        $this->columns = [];
        $this->sql = null;
        $this->limit = null;
        $this->orderBy = null;
        $this->where = null;
        $this->isOrWhere = false;
    }

    /**
     * @return string|null
     */
    public function getTable(): ?string
    {
        return $this->table;
    }


    public function from(string $table_name): static
    {
        $this->resetQuery();
        list($table, $alias) = $this->detectAlias($table_name);
        $this->table = $table;
        $this->alias = $alias;
        return $this;
    }

    public function join($table, string $localColumn, string $foreignColumn, QueryJoinType $join = null, $onTable = null): static
    {
        is_null($join) && $join = QueryJoinType::InnerJoin;

        list($localColumn, $localTable) = $this->detectColumn($localColumn);
        list($foreignColumn, $foreignTable) = $this->detectColumn($foreignColumn);

        is_null($localTable) && $localTable = $onTable ?? $this->table;
        is_null($foreignTable) && $foreignTable = $table;

        $this->joins[] = "{$join->value} `{$table}` ON `{$localTable}`.`{$localColumn}` = `{$foreignTable}`.`{$foreignColumn}`";
        return $this;
    }

    public function select(array $columns): static
    {

        foreach ($columns as $temp) {
            list($field, $alias) = $this->detectAlias($temp);
            list($column, $table) = $this->detectColumn($field);

            if ($column) {
                if ($table) {
                    $column != "*" && $column = "`{$column}`";
                    $column = "`{$table}`.{$column}";
                } else {
                    $column = "`{$column}`";
                }
                $this->columns[$field] = trim($column) . ($alias ? " as {$alias}" : "");
            }
        }
        return $this;
    }

    /**
     * @param string $table
     * @return array {
     * @type string $table
     * @type string|null $alias
     * }
     */
    protected function detectAlias(string $table): array
    {
        $temp = explode('as', $table);
        $table = $temp[0];
        $alias = $temp[1] ?? null;
        is_null($alias) || $alias = trim($alias);
        return [$table, $alias];
    }

    /**
     * @param string $column
     * @return array {
     * @type string $column ,
     * @type QueryCompareOperator|null $operator ,
     * @type mixed $value ,
     * }
     */
    protected function detectOperator(string $column): array
    {
        $operator = null;
        $value = null;
        if (preg_match("/(" . implode('|', array_map(fn($dt) => preg_match("(\w)", $dt->value) ? " {$dt->value}" : $dt->value, QueryCompareOperator::cases())) . ")(\s*.*)?/i", $column, $matches)) {
            $operator = QueryCompareOperator::tryFrom(strtoupper(trim($matches[1])));
            $column = str_replace($matches[0], '', $column);
            $value = isset($matches[2]) ? trim($matches[2]) : null;
        }

        return [$column, $operator, $value];
    }

    /**
     * @param string $column
     * @return array {
     * @type string $column
     * @type string|null $table
     * }
     */
    protected function detectColumn(string $column): array
    {
        $temp = explode('.', $column);
        if (count($temp) == 1) {
            $table = null;
            $column = trim($temp[0]);
        } else {
            $table = trim($temp[0]);
            $column = trim($temp[1]);
        }
        return [$column, $table];
    }

    protected function addWhere(string|array|int|float $column, $value = null, QueryCompareOperator $operator = null, QueryConditionType $type = null): array
    {
        is_null($type) && $type = QueryConditionType::AND;
        if (is_numeric($column)) {
            $whereSql = "`id` = $column";
        } elseif (is_array($column)) {
            $whereSql = [];
            foreach ($column as $temp) {
                if (is_array($temp)) {
                    $whereSql[] = $this->addWhere($temp);
                } else {
                    $whereSql[] = $this->addWhere($column[0], $column[1] ?? null, $column[2] ?? null, $column[3] ?? null);
                    break;
                }
            }
        } else {

            list($column, $columnOperator, $columnValue) = $this->detectOperator($column);

            list($column, $table) = $this->detectColumn($column);

            is_null($table) && $table = $this->table;

            if (is_null($table)) {

                $column = "`{$column}`";

            } else {

                $column = "`{$table}`.`{$column}`";
            }

            /*use condition on column input*/
            if ($columnOperator && $columnValue) {

                $whereSql = "{$column} {$columnOperator->value} {$columnValue}";

            } else {

                $operator = $columnOperator ?? $operator ?? (is_null($value) ? QueryCompareOperator::IsNull : QueryCompareOperator::Equal);

                if (in_array($operator->value, [QueryCompareOperator::IsNull->value, QueryCompareOperator::IsNotNull->value])) {

                    $whereSql = "{$column} {$operator->value}";

                } elseif (in_array($operator->value, [QueryCompareOperator::In->value, QueryCompareOperator::NotIn->value])) {

                    is_array($value) || $value = [$value];

                    $value = implode(', ', $value);

                    $whereSql = "{$column} {$operator->value} ({$value})";

                } else {
                    $whereSql = "{$column} {$operator->value} '{$value}'";
                }
            }
        }

        return [
            $type->value,
            $whereSql
        ];
    }

    public function where(): static
    {
        $args = func_get_args();
        $this->wheres[] = $this->addWhere($args[0], $args[1] ?? null, $args[2] ?? null, QueryConditionType::AND);
        return $this;
    }

    public function orWhere(): static
    {
        $args = func_get_args();
        $this->wheres[] = $this->addWhere($args[0], $args[1] ?? null, $args[2] ?? null, QueryConditionType::OR);
        return $this;
    }

    protected function buildWhere($wheres): string
    {
        $sql = "";
        if ($wheres) {
            foreach ($wheres as $where) {
                if (empty($where)) continue;
                list($type, $condition) = $where;

                if (is_array($condition)) {
                    if (count($condition) > 1) {
                        $sql .= (empty($sql) ? "" : " $type ") . " (" . $this->buildWhere($condition) . ") ";
                    } else {
                        $sql .= (empty($sql) ? "" : " $type ") . $this->buildWhere($condition);
                    }

                } else {
                    $sql .= (empty($sql) ? "" : " $type ") . $condition;
                }
            }
        }
        return $sql;
    }

    public function toUpdateSql($fields = [], $wheres = []): string
    {
        $whereArr = [];
        if ($wheres) {
            $whereArr = $this->addWhere($wheres[0], $wheres[1] ?? null, $wheres[2] ?? null, $wheres[3] ?? null);
        }

        $whereSql = $this->buildWhere(array_merge($this->wheres, [$whereArr]));

        if ($whereSql = trim($whereSql)) {
            $whereSql = "WHERE {$whereSql}";
        }

        $setArr = [];
        foreach ($fields as $column => $value) {
            if (is_null($value)) {
                $setArr[] = "`$column` = NULL";
            } else {
                $setArr[] = "`$column` = '{$value}'";
            }
        }
        $setSql = implode(', ', $setArr);

        return "UPDATE `{$this->table}` SET {$setSql} {$whereSql}";
    }

    public function toInsertSql($fields = []): string
    {
        $keys = implode('`, `', array_keys($fields));
        $values = [];
        foreach ($fields as $field => $value) {
            $values[] = "'$value'";
        }
        $valueSql = implode(', ', $values);

        return "INSERT INTO `{$this->table}` (`{$keys}`) VALUES ({$valueSql})";
    }

    function toSelectSql(): string
    {
        return (string)$this;
    }

    function toDeleteSql(...$wheres): string
    {
        $whereArr = $this->addWhere($wheres[0] ?? null, $wheres[1] ?? null, $wheres[2] ?? null, QueryConditionType::AND);
        $whereSql = '';
        if ($whereArr) {
            $whereSql = $this->buildWhere([$whereArr]);
        }

        if ($whereSql = trim($whereSql)) {
            $whereSql = "WHERE {$whereSql}";
        }
        return "DELETE FROM `{$this->table}` {$whereSql}";
    }

    function __toString(): string
    {

        if ($this->columns) {
            $select = implode(', ', $this->columns);
        } else {
            $select = "*";
        }
        if ($this->joins) {
            $joinSql = implode(' ', $this->joins);
        } else {
            $joinSql = "";
        }
        $table = "`{$this->table}`";
        if ($this->alias) {
            $table = "`{$this->table}`as `{$this->alias}`";
        }


        $sql = "SELECT $select FROM {$table} {$joinSql} ";

        if ($where = $this->buildWhere($this->wheres)) {
            $sql .= "WHERE " . $where;
        }

        if ($this->orderBy !== null) {
            $sql .= $this->orderBy;
        }

        if ($this->limit !== null) {
            $sql .= $this->limit;
        }
        return $sql;
    }

    public function limit($limit, $offset = null)
    {
        if ($offset == null) {
            $this->limit = " LIMIT {$limit}";
        } else {
            $this->limit = " LIMIT {$limit} OFFSET {$offset}";
        }

        return $this;
    }

    public function orderBy($field_name, $order = 'ASC')
    {
        $field_name = trim($field_name);

        $order = trim(strtoupper($order));

        // validate it's not empty and have a proper valuse
        if ($field_name !== null && ($order == 'ASC' || $order == 'DESC')) {
            if ($this->orderBy == null) {
                $this->orderBy = " ORDER BY $field_name $order";
            } else {
                $this->orderBy .= ", $field_name $order";
            }

        }

        return $this;
    }

}