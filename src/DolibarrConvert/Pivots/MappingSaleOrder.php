<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Pivots;

/**
 * @property string transcann_client_id
 */
class MappingSaleOrder extends ModelPivot
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