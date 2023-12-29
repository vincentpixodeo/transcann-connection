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
     * get Data saved
     * @param ...$agruments
     * @return ?$this
     */
    function fetch(...$agruments): ?static;
}