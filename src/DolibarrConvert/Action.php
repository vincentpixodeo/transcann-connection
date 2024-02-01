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
 * @property string action
 * @property string payload
 * @property int status
 * @property int retries
 * @property int last_result_id
 * @property int last_result_status
 */
class Action extends AbstractObjectData implements ObjectDataInterface, CanSaveDataInterface
{
    const STATUS_INIT = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_PROCESSED = 2;

    use CanSaveDataByDatabaseTrait;

    public function getMainTable(): string
    {
        return 'transcannconnection_actions';
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }
}