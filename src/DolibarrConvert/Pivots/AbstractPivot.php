<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Pivots;

use WMS\Xtent\Contracts\AbstractObjectData;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataByDatabaseTrait;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataInterface;

/**
 * @property int fk_object_id
 * @property string transcann_id
 * @property string transcan_meta_id
 * @property string transcan_payload
 */
abstract class AbstractPivot extends AbstractObjectData implements ObjectDataInterface, CanSaveDataInterface
{
//    use CanSaveDataByLogTrait;
    use CanSaveDataByDatabaseTrait;
}