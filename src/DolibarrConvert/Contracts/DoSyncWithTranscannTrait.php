<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Contracts;

use WMS\Xtent\Contracts\ObjectDataInterface;


trait DoSyncWithTranscannTrait
{
//    use CanSaveDataByLogTrait;
    use CanSaveDataByDatabaseTrait;

    private $_mappingInstance = null;

    abstract function getMappingClass(): string;

    function getMappingInstance(array $data = []): ObjectDataInterface&CanSaveDataInterface
    {
        $primaryKey = $this->getPrimaryKey();
        if (is_null($this->_mappingInstance)) {
            $this->_mappingInstance = new ($this->getMappingClass())([
                'fk_object_id' => $this->{$primaryKey}
            ]);

            if ($this->{$primaryKey} && $exist = $this->_mappingInstance->fetch($this->{$primaryKey}, 'fk_object_id')) {

                $this->_mappingInstance = $exist;
            }

            $this->_mappingInstance->addData($data);

            if ($this->{$primaryKey} && !$this->_mappingInstance->getData($this->_mappingInstance->getPrimaryKey())) {
                $this->_mappingInstance->save();
            }

        }

        return $this->_mappingInstance;
    }

}