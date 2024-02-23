<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Contracts\AbstractObjectData;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataByDatabaseTrait;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataInterface;

/**
 * @property int rowid
 * @property int fk_expeditiondet
 * @property string eatby
 * @property string sellby
 * @property string batch
 * @property float qty
 * @property int fk_origin_stock
 */
class ShippingBatch extends AbstractObjectData implements ObjectDataInterface, CanSaveDataInterface
{
    use CanSaveDataByDatabaseTrait;

    public function getMainTable(): string
    {
        return 'expeditiondet_batch';
    }

    public function getPrimaryKey(): string
    {
        return 'rowid';
    }
}