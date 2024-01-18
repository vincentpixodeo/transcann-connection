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
 * @property int id
 * @property int action_id
 * @property string payload
 * @property string response
 * @property string error
 * @property int status
 */
class ActionResult extends AbstractObjectData implements ObjectDataInterface, CanSaveDataInterface
{
    const STATUS_START = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = 2;

    use CanSaveDataByDatabaseTrait;

    public function getMainTable(): string
    {
        return 'transcannconnection_action_results';
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }
}