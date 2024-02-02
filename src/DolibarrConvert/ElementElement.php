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
 * @property int $rowid
 * @property int $fk_source
 * @property string $sourcetype
 * @property int $fk_target
 * @property string $targettype
 */
class ElementElement extends AbstractObjectData implements ObjectDataInterface, CanSaveDataInterface
{
    use CanSaveDataByDatabaseTrait;

    public function getMainTable(): string
    {
        return 'element_element';
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }
}