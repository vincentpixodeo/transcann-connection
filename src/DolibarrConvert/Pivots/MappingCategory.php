<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Pivots;

class MappingCategory extends ModelPivot
{
    public function getMainTable(): string
    {
        return 'transcannconnection_mapping_categories';
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }
}