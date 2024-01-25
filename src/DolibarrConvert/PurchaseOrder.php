<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use DateTime;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Preparation;
use WMS\Xtent\Data\Reception;
use WMS\Xtent\DolibarrConvert\Pivots\MappingPurchaseOrder;

/**
 * dolibarr data 'fourn/class/fournisseur.commande.class.php'
 * $table llx_commande_fournisseur
 * @property int rowid
 * @property string ref
 * @property string ref_ext
 * @property string ref_supplier
 * @property int fk_soc
 * @property int fk_projet
 * @property DateTime date_valid
 * @property DateTime date_approve
 *
 * property on llx_commande_fournisseur_dispatch table
 * @property string batch
 * @property int fk_product
 * @property int fk_entrepot
 * @property int qty
 * @property string comment
 */
class PurchaseOrder extends Model
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
        return 'commande_fournisseur';
    }

    function getAppendAttributes(): array
    {
        return [];
    }

    function getMappingClass(): string
    {
        return MappingPurchaseOrder::class;
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
            $api = new \WMS\Xtent\Apis\Reception();
            $transcannId = $mapping->transcann_id ?? null;
            $dataSend += $data;
            if ($transcannId) {
                if ($api->put($transcannId, $dataSend)) {
                    $transcannInstance = new Reception($api->getResponse()->getData());
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
                    $transcannInstance = new Reception($api->getResponse()->getData());
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