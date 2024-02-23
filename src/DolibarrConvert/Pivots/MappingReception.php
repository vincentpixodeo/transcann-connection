<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Pivots;

use WMS\Xtent\Data\Reception;

class MappingReception extends ModelPivot
{
    protected ?string $_payloadClass = Reception::class;

    public function getMainTable(): string
    {
        return 'transcannconnection_mapping_receptions';
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }
}