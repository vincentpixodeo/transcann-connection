<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Contracts;

use WMS\Xtent\Contracts\ObjectDataInterface;


trait DoSyncWithTranscannByLogTrait
{
    use CanSaveDataByLogTrait;

    private $_mappingInstance = null;

    abstract function getMappingClass(): string;

    function getMappingInstance(array $data = []): ObjectDataInterface&CanSaveDataInterface
    {
        if (is_null($this->_mappingInstance)) {
            $this->_mappingInstance = new ($this->getMappingClass())([
                'fk_object_id' => $this->rowid
            ]);
            if ($this->rowid) {
                $this->_mappingInstance->fetch();
            }
            $this->_mappingInstance->addData($data);
        }

        return $this->_mappingInstance;
    }

}