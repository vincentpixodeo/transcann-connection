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
    const INTEGRATE_STATUS_INIT = 0;
    const INTEGRATE_STATUS_OK = 1;
    const INTEGRATE_STATUS_FAIL = 2;
    const INTEGRATE_STATUS_COMPLETED = 3;

    const PROPERTY_FK_OBJECT_ID = 'fk_object_id';
    const PROPERTY_TRANSCAN_ID = 'transcan_id';
    const PROPERTY_TRANSCAN_META_ID = 'transcan_meta_id';
    const PROPERTY_TRANSCAN_PAYLOAD = 'transcan_payload';
    const PROPERTY_TRANSCAN_INTEGRATE_STATUS = 'transcan_integrate_status';

    protected ?string $_payloadClass = null;
    protected ?ObjectDataInterface $_payloadInstance = null;
//    use CanSaveDataByLogTrait;
    use CanSaveDataByDatabaseTrait;

    public function __construct(array $data = [], string $transcanClass = null)
    {
        parent::__construct($data);

        is_null($transcanClass) || $this->_payloadClass = $transcanClass;
    }

    function getPayload(): ?ObjectDataInterface
    {
        if ($this->transcan_payload && is_null($this->_payloadInstance)) {

            $this->_payloadInstance = $this->_payloadClass ? new $this->_payloadClass() : new class extends AbstractObjectData {
            };
            $this->_payloadInstance->addData(json_decode($this->transcan_payload, true));
        }
        return $this->_payloadInstance;
    }
}