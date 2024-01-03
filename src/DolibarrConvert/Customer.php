<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Contracts\AbstractObjectData;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Client;
use WMS\Xtent\Data\Enums\WarehousePartyCategory;
use WMS\Xtent\DolibarrConvert\Contracts\ConvertTranscanInteface;
use WMS\Xtent\DolibarrConvert\Contracts\ConvertTranscanTrait;
use WMS\Xtent\DolibarrConvert\Contracts\DoSyncWithTranscannByLogTrait;
use WMS\Xtent\DolibarrConvert\Contracts\DoSyncWithTranscannInterface;
use WMS\Xtent\DolibarrConvert\Pivots\MappingCustomer;

/**
 * @property int rowid
 * @property string ref
 * @property string label
 * @property string description
 * @property string price
 * $table llx_societe
 */
class Customer extends AbstractObjectData implements ConvertTranscanInteface, ObjectDataInterface, DoSyncWithTranscannInterface
{
    use ConvertTranscanTrait;
    use DoSyncWithTranscannByLogTrait;

    function getMapAttributes(): array
    {
        return [
            'nom' => 'Name',
            'name_alias' => 'ShortName',
        ];
    }

    function getTranscanInstance(): string|ObjectDataInterface
    {
        return Client::class;
    }

    protected function getMainTable(): string
    {
        return 'customers';
    }


    function getAppendAttributes(): array
    {
        return [
            'WarehousePartyCategory' => WarehousePartyCategory::Recipient->value,
            'Id' => $this->rowid
        ];
    }

    function getMappingClass(): string
    {
        return MappingCustomer::class;
    }

    function updateDataFromTranscann(array $data = []): bool
    {
        return true;
    }

    function pushDataToTranscann(array $data = []): bool
    {
        $mapping = $this->getMappingInstance()->fetch();

        /* Action push data to Transcann*/
        if ($mapping) {
            $dataSend = $this->convertToTranscan()->toArray();
            $api = new \WMS\Xtent\Apis\Party();
            $transcannId = $mapping->transcann_id ?? null;
            $dataSend += $data;
            if ($transcannId) {
                if ($api->put($transcannId, $dataSend)) {
                    $transcannInstance = new Client($api->getResponse()->getData());
                    $mapping->save([
                        'transcann_id' => $transcannInstance->Id,
                        'transcan_meta_id' => $transcannInstance->_MetaId_,
                        'transcan_payload' => json_encode($transcannInstance->toArray())
                    ]);
                } else {
                    dd($api->getClient()->getCurrentLog());
                }
            } else {
                if ($api->create($dataSend)) {
                    $transcannInstance = new Client($api->getResponse()->getData());
                    $mapping->save([
                        'transcann_id' => $transcannInstance->Id,
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