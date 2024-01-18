<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Pivots;

use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataInterface;

class MappingWarehouse extends AbstractPivot implements ObjectDataInterface, CanSaveDataInterface
{

    public function getMainTable(): string
    {
        return 'transcannconnection_mapping_warehouses';
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }
}