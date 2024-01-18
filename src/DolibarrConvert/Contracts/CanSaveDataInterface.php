<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Contracts;

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