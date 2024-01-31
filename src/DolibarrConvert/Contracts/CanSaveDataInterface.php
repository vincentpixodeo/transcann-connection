<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Contracts;

use Generator;

interface CanSaveDataInterface
{
    /**
     * do save data
     * @param array $data
     * @return bool
     */
    function save(array $data = []): bool;

    /**
     * @param $id
     * @param $field
     * @param callable|null $queryBuilderCallback
     * @return $this|null
     */
    function fetch($id = null, $field = null, callable $queryBuilderCallback = null): ?static;

    /**
     * @param array $condition
     * @param callable|null $queryBuilderCallback
     * @return Generator
     */
    function list(array $condition = [], callable $queryBuilderCallback = null): Generator;

    /**
     * get Primary Key
     * @return string
     */
    public function getPrimaryKey(): string;

    /**
     * get Value of primary key
     * @return string|int|null
     */
    public function id(): string|int|null;
}