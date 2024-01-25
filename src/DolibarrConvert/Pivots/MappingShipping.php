<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Pivots;

class MappingShipping extends ModelPivot
{
    public function getMainTable(): string
    {
        return 'transcannconnection_mapping_shippings';
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }
}