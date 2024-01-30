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
     * @return $this|null
     */
    function fetch($id = null, $field = null): ?static;

    /**
     * get list
     * @param array $condition
     * @return Generator
     */
    function list(array $condition = [], int $limit = null, int $offset = null): Generator;

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