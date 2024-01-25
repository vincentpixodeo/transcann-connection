<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Pivots;

class MappingUnit extends ModelPivot
{
    public function getMainTable(): string
    {
        return 'transcannconnection_mapping_units';
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }
}