<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use DateTime;
use WMS\Xtent\Apis\IntegrationWebServices\Receptions as IntegrationWebServices_Receptions;
use WMS\Xtent\Apis\QueryWebServices\CheckFlowIntegrationStatus;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Item;
use WMS\Xtent\Data\Reception as TranscannReception;
use WMS\Xtent\DolibarrConvert\Pivots\MappingReception;

/**
 * @property int $rowid
 * @property string $ref
 * @property int $entity
 * @property int $fk_soc
 * @property int $fk_projet
 * @property int $ref_supplier
 * @property string $ref_ext
 * @property int $fk_statut
 * @property int $billed
 * @property DateTime date_delivery
 * @property DateTime date_reception
 * @property string tracking_number
 * @property int weight_units
 * @property float weight
 * dolibarr data 'fourn/class/fournisseur.commande.class.php'
 * $table llx_reception
 */
class Reception extends Model
{

    function getMapAttributes(): array
    {
        return [
        ];
    }

    /**
     * @return PurchaseOrder[]|null
     */
    function lines(): ?array
    {
        $order = new PurchaseOrder();
        $orderTable = getDbPrefix() . ltrim($order->getMainTable(), getDbPrefix());
        $joinTable = 'llx_commande_fournisseur_dispatch';
        $select = [
            "{$orderTable}.*",
            "{$joinTable}.batch",
            "{$joinTable}.fk_product",
            "{$joinTable}.fk_entrepot",
            "{$joinTable}.qty",
            "{$joinTable}.comment",
        ];
        $sqlSelect = implode(', ', $select);

        $where = [
            $joinTable . '.fk_reception' => $this->id(),
        ];

        $whereArr = [];

        foreach ($where as $k => $vl) {
            $whereArr[] = "{$k} = '{$vl}'";
        }
        $sqlWhere = implode(' AND ', $whereArr);

        $query = "SELECT {$sqlSelect} FROM {$orderTable} INNER JOIN {$joinTable} ON {$joinTable}.fk_commande = {$orderTable}.rowid WHERE {$sqlWhere}";


        $db = getDbInstance();
        $results = $db->query($query);
        $orders = [];
        if ($results) {
            while ($row = $db->fetch_object($results)) {
                $orders[] = new PurchaseOrder((array)$row);
            }
            return $orders;
        }
        return null;
    }

    function getTranscanInstance(): string|ObjectDataInterface
    {
        return new TranscannReception();
    }

    public function getMainTable(): string
    {
        return 'reception';
    }

    /**
     * @param PurchaseOrder[] $lines
     * @return array
     */
    function getDataSendToTranscan(array $lines): array
    {

        $dataSend = $this->convertToTranscan()->toArray();
        $dataSend = $dataSend + [
                "Order" => $lines[0]->ref,
                "SupplierReference" => $lines[0]->ref,
                "ClientCodeId" => 2000,
                "MovementCodeId" => "ENT",
                "AppointmentDate" => null,
                "ArrivalDate" => $lines[0]->date_valid?->format('YmdHi'),
                "DateOfPlannedReceiving" => $lines[0]->date_approve?->format('YmdHi'),
                "Comments" => "INTEGRE PAR API"
            ];

        $EdiReceptionDetailsList = [];

        foreach ($lines as $index => $order) {

            $productMapping = (new Product(['rowid' => $order->fk_product]))->getMappingInstance();
            if (!$productMapping || !$productMapping->transcan_id) {
                throw new \Exception("plz sync the product #{$order->fk_product} before");
            }
            /** @var Item|null $productTranscanInstance */
            $productTranscanInstance = $productMapping->getPayload();

            $EdiReceptionDetailsList[] = [
                "BatchNumber" => $order->batch,
                "Comments" => $order->comment,
                "CRD" => null,
                "CurrencyId" => null,
                "CustomizedDate1" => null,
                "CustomizedDate2" => null,
                "CustomizedDate3" => null,
                "CustomizedDate4" => null,
                "CustomizedDate5" => null,
                "CustomizedField1" => null,
                "CustomizedField10" => null,
                "CustomizedField2" => null,
                "CustomizedField3" => null,
                "CustomizedField4" => null,
                "CustomizedField5" => null,
                "CustomizedField6" => null,
                "CustomizedField7" => null,
                "CustomizedField8" => null,
                "CustomizedField9" => null,
                "DAEDSANumber" => null,
                "DAEDSAType" => null,
                "ExpectedFullPallet" => null,
                "ExpectedParcel" => "1",
                "ExpectedSaleUnit" => null,
                "ExpiryDate" => null,
                "ExternalItemId" => null,
                "ExternalLineNumber" => null,
                "GrossWeight" => null,
                "Id" => 0,
                "Inner" => null,
                "InternalItemId" => $productTranscanInstance?->Id,
                "ItemCode" => $productTranscanInstance?->ItemCode,
                "LayersPerPallet" => null,
                "LineNumber" => $index + 1,
                "MultiReferencePalletId" => null,
                "NetWeight" => $productTranscanInstance?->ParcelNetWeight,
                "Outer" => null,
                "PackagingCode" => null,
                "Pallet" => "PAL0123456789",
                "PalletOccupationDepth" => null,
                "PalletOccupationHeight" => null,
                "PalletOccupationWidth" => null,
                "ParcelsPerLayer" => null,
                "StatusCodeId" => null,
                "Value" => $order->cost_price
            ];
        }

        return $dataSend + [
                "EdiReceptionDetailsList" => $EdiReceptionDetailsList
            ];
    }

    function getAppendAttributes(): array
    {
        return [];

    }

    function getMappingClass(): string
    {
        return MappingReception::class;
    }

    function updateDataFromTranscann(array $data = []): bool
    {

        return true;
    }

    function pushDataToTranscann(array $data = []): bool
    {
        $mapping = $this->getMappingInstance()->fetch();
        $lines = $this->lines();

        /* Action push data to Transcann*/
        if ($mapping && $lines) {
            $dataSend = $this->getDataSendToTranscan($lines);

            $api = new IntegrationWebServices_Receptions();
            $dataSend += $data;

            if ($api->execute([
                'listReceptions' => $dataSend
            ])) {
                $result = $api->getResponse()->getData();
                $transcannId = $result['result']['ResultOfReceptionsIntegration'][0]['XtentReceptionId'] ?? null;
                $transcannMetaId = $result['result']['ResultOfReceptionsIntegration'][0]['SupplierReference'] ?? null;
                $folowId = $result['result']['FlowsId'][0]['FlowID'] ?? null;

                $apiFlow = new CheckFlowIntegrationStatus();
                if ($apiFlow->execute($folowId)) {
                    $checkResult = $apiFlow->getResponse()->getData();
                    if ("OK" == ($checkResult['result']['FlowStatus'] ?? null)) {
                        $mapping->transcan_id = $transcannId;
                        $mapping->transcan_meta_id = $transcannMetaId;
                        $mapping->save();
                    } else {
                        throw new TranscannSyncException(new \Exception('Result Fail'), $apiFlow->getClient()->getLogs());
                    }
                } else {
                    $errors = $apiFlow->getErrors();
                    throw new TranscannSyncException(array_pop($errors), $apiFlow->getClient()->getLogs());
                }
            } else {
                $errors = $api->getErrors();
                throw new TranscannSyncException(array_pop($errors), $api->getClient()->getLogs());
            }
        }
        return false;
    }

    public function getPrimaryKey(): string
    {
        return 'rowid';
    }
}