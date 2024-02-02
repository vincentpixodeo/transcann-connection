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
 * @property int $id
 * @property int $fk_object_id
 * @property string $transcan_id
 * @property string $transcan_meta_id
 * @property string $transcan_payload
 * @property int $transcan_integrate_status
 */
abstract class ModelPivot extends AbstractObjectData implements ObjectDataInterface, CanSaveDataInterface
{
    protected ?string $_payloadClass = null;
    protected ?ObjectDataInterface $_payloadInstance = null;
//    use CanSaveDataByLogTrait;
    use CanSaveDataByDatabaseTrait;

    public function __construct(array $data = [], string $transcanClass = null)
    {
        parent::__construct($data);

        $this->_payloadClass = $transcanClass;
    }

    function getPayload(): ?ObjectDataInterface
    {
        if ($this->transcan_payload && is_null($this->_payloadInstance)) {
            $this->_payloadInstance = new $this->_payloadClass(json_decode($this->transcan_payload, true));
        }
        return $this->_payloadInstance;
    }
}