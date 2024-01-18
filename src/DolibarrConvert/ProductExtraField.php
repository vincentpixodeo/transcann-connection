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
 * @property int contenance
 * @property int unitecarton
 * @property int cartonplan
 * @property int planpalette
 * @property float poidsbrut
 * @property float poidsnet
 */
class ProductExtraField extends AbstractObjectData implements ObjectDataInterface, CanSaveDataInterface
{

    use CanSaveDataByDatabaseTrait;

    public function getMainTable(): string
    {
        return 'product_extrafields';
    }

    public function getPrimaryKey(): string
    {
        return 'fk_object';
    }
}