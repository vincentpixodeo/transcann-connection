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
 * @property int $id
 * @property string $action
 * @property string $payload
 * @property int $status
 * @property int $action_id
 */
class WebhookEvent extends AbstractObjectData implements ObjectDataInterface, CanSaveDataInterface
{
    use CanSaveDataByDatabaseTrait;

    public function getMainTable(): string
    {
        return 'llx_transcannconnection_events';
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }
}