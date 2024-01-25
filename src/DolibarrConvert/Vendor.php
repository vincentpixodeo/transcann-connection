<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Client;
use WMS\Xtent\Data\Enums\WarehousePartyCategory;
use WMS\Xtent\DolibarrConvert\Pivots\MappingVendor;

/**
 * @property int rowid
 * @property string ref
 * @property string label
 * @property string description
 * @property string price
 * $table llx_societe
 */
class Vendor extends Model
{

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

    public function getMainTable(): string
    {
        return 'societe';
    }

    function getMappingClass(): string
    {
        return MappingVendor::class;
    }

    function getAppendAttributes(): array
    {
        return [
            'WarehousePartyCategory' => WarehousePartyCategory::Supplier->value,
            'Id' => $this->rowid
        ];
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

    protected function defaultCondition(): array
    {
        return [
            'fournisseur' => 1
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'rowid';
    }
}