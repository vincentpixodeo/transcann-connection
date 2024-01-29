<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Pivots;

class MappingReception extends ModelPivot
{
    public function getMainTable(): string
    {
        return 'transcannconnection_mapping_receptions';
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }
}