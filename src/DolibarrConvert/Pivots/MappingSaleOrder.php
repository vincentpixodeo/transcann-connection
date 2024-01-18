<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Pivots;

use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataInterface;

/**
 * @property string transcann_client_id
 */
class MappingSaleOrder extends AbstractPivot implements ObjectDataInterface, CanSaveDataInterface
{
    public function getMainTable(): string
    {
        return 'transcannconnection_mapping_sale_orders';
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }
}