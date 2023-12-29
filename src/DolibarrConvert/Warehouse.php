<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Contracts\AbstractObjectData;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataByLogTrait;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataInterface;
use WMS\Xtent\DolibarrConvert\Contracts\ConvertTranscanInteface;
use WMS\Xtent\DolibarrConvert\Contracts\ConvertTranscanTrait;
use WMS\Xtent\DolibarrConvert\Contracts\DoSyncWithTranscannByLogTrait;
use WMS\Xtent\DolibarrConvert\Pivots\MappingWarehouse;

/**
 * htdocs/product/stock/class/entrepot.class.php
 * $table llx_entrepot
 */
class Warehouse extends AbstractObjectData implements ConvertTranscanInteface, ObjectDataInterface, CanSaveDataInterface
{
    use ConvertTranscanTrait;
    use DoSyncWithTranscannByLogTrait;
    use CanSaveDataByLogTrait;

    function getMapAttributes(): array
    {
        return [];
    }

    function getTranscanInstance(): string|ObjectDataInterface
    {
        return new \WMS\Xtent\Data\Address\Warehouse();
    }

    protected function getMainTable(): string
    {
        return 'warehouses';
    }

    function getMappingClass(): string
    {
        return MappingWarehouse::class;
    }
}