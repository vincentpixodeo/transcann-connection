<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Contracts;

use WMS\Xtent\Database\Builder\QueryBuilder;

trait HasSqlBuilder
{
    protected QueryBuilder|null $_sqlBuilder = null;
    protected static array $_sqlEventBooted = [];
    protected static array $_sqlEvents = [];


    protected function buildSelectSql(array $where = [], string $method = null, bool $appendInstance = false): QueryBuilder
    {
        $table = getDbPrefix() . ltrim($this->getMainTable(), getDbPrefix());

        if ($method == 'init') {
            $this->_sqlBuilder = new QueryBuilder();
            $this->_sqlBuilder->from($table);
        } else {
            $this->buildSelectSql($this->defaultCondition(), 'init');
        }

        $appendInstance || $appendInstance = $method == 'init';

        if ($appendInstance) {
            $sqlBuilder = &$this->_sqlBuilder;
        } else {
            $sqlBuilder = clone $this->_sqlBuilder;
        }

        foreach ($where as $k => $vl) {
            if (is_array($vl)) {
                $sqlBuilder->where(...$vl);
            } elseif (is_numeric($k)) {
                $sqlBuilder->where($vl);
            } else {
                $sqlBuilder->where("{$k}", $vl);
            }
        }

        if (self::$_sqlEvents[static::class][$method] ?? null) {
            foreach (self::$_sqlEvents[static::class][$method] as $action) {
                $action($sqlBuilder);
            }
        }

        return $sqlBuilder;
    }

    /**
     * add hook after build sql
     * @param callable $callback
     * @return void
     */
    static public function sqlEvent($method, callable $callback): void
    {
        /*only boot once*/
        if ((self::$_sqlEventBooted[static::class] ?? false) && $method == 'init') {
            return;
        }

        isset(self::$_sqlEvents[static::class]) || self::$_sqlEvents[static::class] = [];
        isset(self::$_sqlEvents[static::class][$method]) || self::$_sqlEvents[static::class][$method] = [];
        self::$_sqlEvents[static::class][$method][] = $callback;
        $method == 'init' && self::$_sqlEventBooted[static::class] = true;
    }
}