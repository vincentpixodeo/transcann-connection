<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Contracts\AbstractObjectData;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\DolibarrConvert\Contracts\ConvertTranscanInteface;
use WMS\Xtent\DolibarrConvert\Contracts\ConvertTranscanTrait;

/**
 * htdocs/product/stock/class/entrepot.class.php
 */
class Warehouse extends AbstractObjectData implements ConvertTranscanInteface, ObjectDataInterface
{
    use ConvertTranscanTrait;

    function getMapAttributes(): array
    {
        return [];
    }

    function getTranscanInstance(): string|ObjectDataInterface
    {
        return new \WMS\Xtent\Data\Address\Warehouse();
    }
}