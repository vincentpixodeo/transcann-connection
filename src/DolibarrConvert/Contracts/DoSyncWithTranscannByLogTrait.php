<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Contracts;

use WMS\Xtent\Apis\Item;
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

    function updateDataFromTranscann(array $data = []): bool
    {
        $mapping = $this->getMappingInstance()->fetch();

        /* Action save data from Transcann*/
        if ($mapping) {
            /*Fetch Data Transcann*/
            $api = new Item\GetByKeys();
            if ($api->load($mapping->transcann_id)) {
                $objectData = new \WMS\Xtent\Data\Item($api->getResponse()->getData());
                $dataSave = $this->createFromTranscan($objectData)->toArray();
                $this->save(array_merge($data, $dataSave));
            } else {
                dd($api->getErrors());
            }
        }
        return false;
    }

    function pushDataToTranscann(array $data = []): bool
    {
        $mapping = $this->getMappingInstance()->fetch();

        /* Action push data to Transcann*/
        if ($mapping) {
            $dataSend = $this->convertToTranscan()->toArray();
            $api = new Item();
            $transcannId = $mapping->transcann_id ?? null;
            $dataSend += $data;
            if ($transcannId) {
                if ($transcannInstance = $api->put($transcannId, $dataSend)) {
                    $mapping->save([
                        'transcann_id' => $transcannInstance->ClientCodeId,
                        'transcann_client_id' => $transcannInstance->ClientCodeId,
                        'transcan_meta_id' => $transcannInstance->_MetaId_,
                        'transcan_payload' => json_encode($transcannInstance->toArray())
                    ]);
                } else {
                    dd($api->getClient()->getCurrentLog());
                }
            } else {
                if ($transcannInstance = $api->create($dataSend)) {
                    $mapping->save([
                        'transcann_id' => $transcannInstance->Id,
                        'transcann_client_id' => $transcannInstance->ClientCodeId,
                        'transcan_meta_id' => $transcannInstance->_MetaId_,
                        'transcan_payload' => json_encode($transcannInstance->toArray())
                    ]);
                } else {
                    dd($api->getClient()->getCurrentLog());
                }
            }
        }
        return false;
    }
}