<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Pivots;

class MappingWarehouse extends ModelPivot
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