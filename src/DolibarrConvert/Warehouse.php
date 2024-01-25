<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\DolibarrConvert\Pivots\MappingWarehouse;

/**
 * htdocs/product/stock/class/entrepot.class.php
 * $table llx_entrepot
 */
class Warehouse extends Model
{

    function getMapAttributes(): array
    {
        return [];
    }

    function getTranscanInstance(): string|ObjectDataInterface
    {
        return new \WMS\Xtent\Data\Address\Warehouse();
    }

    public function getMainTable(): string
    {
        return 'entrepot';
    }

    function getMappingClass(): string
    {
        return MappingWarehouse::class;
    }

    function getAppendAttributes(): array
    {
        return [];
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
            $api = new \WMS\Xtent\Apis\Warehouse();
            $transcannId = $mapping->transcann_id ?? null;
            $dataSend += $data;
            if ($transcannId) {
                if ($api->put($transcannId, $dataSend)) {
                    $transcannInstance = new \WMS\Xtent\Data\Address\Warehouse($api->getResponse()->getData());
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
                    $transcannInstance = new \WMS\Xtent\Data\Address\Warehouse($api->getResponse()->getData());
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

    public function getPrimaryKey(): string
    {
        return 'rowid';
    }
}