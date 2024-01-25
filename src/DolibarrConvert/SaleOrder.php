<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Preparation;
use WMS\Xtent\DolibarrConvert\Pivots\MappingSaleOrder;

/**
 * dolibarr data 'fourn/class/fournisseur.commande.class.php'
 * $table llx_commande
 */
class SaleOrder extends Model
{

    function getMapAttributes(): array
    {
        return [];
    }

    function getTranscanInstance(): string|ObjectDataInterface
    {
        return new Preparation();
    }

    public function getMainTable(): string
    {
        return 'commande';
    }

    function getAppendAttributes(): array
    {
        return [];
    }

    function getMappingClass(): string
    {
        return MappingSaleOrder::class;
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
            $api = new \WMS\Xtent\Apis\Preparation();
            $transcannId = $mapping->transcann_id ?? null;
            $dataSend += $data;
            if ($transcannId) {
                if ($api->put($transcannId, $dataSend)) {
                    $transcannInstance = new Preparation($api->getResponse()->getData());
                    $mapping->save([
                        'transcann_id' => $transcannInstance->Id,
                        'transcann_client_id' => $transcannInstance->ClientCodeId,
                        'transcan_meta_id' => $transcannInstance->_MetaId_,
                        'transcan_payload' => json_encode($transcannInstance->toArray())
                    ]);
                } else {
                    dd($api->getClient()->getCurrentLog());
                }
            } else {
                if ($api->create($dataSend)) {
                    $transcannInstance = new Preparation($api->getResponse()->getData());
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

    public function getPrimaryKey(): string
    {
        return 'rowid';
    }
}