<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Contracts;


use WMS\Xtent\Contracts\ObjectDataInterface;


interface ConvertTranscanInteface
{
    /**
     * @param ObjectDataInterface $item
     * @return static
     */
    public function createFromTranscan($item);

    public function convertToTranscan();

    function getMapAttributes(): array;

    function getTranscanInstance(): string|ObjectDataInterface;
}