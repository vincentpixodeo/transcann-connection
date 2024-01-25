<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Apis\IntegrationWebServices\Items;
use WMS\Xtent\Apis\QueryWebServices\CheckFlowIntegrationStatus;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Item;
use WMS\Xtent\DolibarrConvert\Pivots\MappingProduct;

/**
 * @property int rowid
 * @property string ref
 * @property string label
 * @property string description
 * @property string price
 * @property string barcode
 * $table llx_product
 */
class Product extends Model
{

    public ?ProductExtraField $extraField = null;

    function getMapAttributes(): array
    {
        return [
            'label' => 'Description',
            'ref' => 'ItemCode',
            'price' => 'Value',
        ];
    }

    function getTranscanInstance(): string|ObjectDataInterface
    {
        return Item::class;
    }

    public function getMainTable(): string
    {
        return 'product';
    }

    function getMappingClass(): string
    {
        return MappingProduct::class;
    }

    function getAppendAttributes(): array
    {
        $extraField = $this->fetchExtraFields();
        return [
            'ClientCodeId' => 2000,
            "ExternalReference" => null,
            "BatchManagement" => "L",
            "RotationCode" => "B",
//            "UnitCode" => $extraField?->contenance,
            "Comments" => null,
            "Inner" => 1,
            "Outer" => $extraField?->unitecarton,
            "ParcelsPerLayer" => 40,
            "LayersPerPallet" => 6,
            "SUWidth" => 20.0,
            "SUDepth" => 40.0,
            "SUHeight" => 5.0,
            "Width" => 50,
            "Depth" => 60,
            "Height" => 30,
            "PalletOccupationWidth" => 100,
            "PalletOccupationDepth" => 100,
            "PalletOccupationHeight" => 120,
            "ParcelGrossWeight" => 2.6,
            "ParcelNetWeight" => 2.6,
            "SUGrossWeight" => $extraField?->poidsbrut,
            "SUNetWeight" => $extraField?->poidsnet,
            "CurrencyId" => "EUR",
            "PackagingCode" => "EUR",
            "InboundStatus" => null,
//            "ReturnStatus" => "IND",
            "OutboundStatus" => null,
//            "SupplierCodeId" => 111,
//            "FamilyCode" => "AAA",
            "EdiItemGencode" => [
                [
                    "ItemGencod" => $this->barcode
                ]
            ]
        ];
    }

    protected function fetchExtraFields(): ?ProductExtraField
    {
        return (new ProductExtraField())->fetch($this->{$this->getPrimaryKey()});
    }

    function updateDataFromTranscann(array $data = []): bool
    {
        return true;
    }

    /**
     * @throws TranscannSyncException
     */
    function pushDataToTranscann(array $data = []): bool
    {
        $this->fetch();

        /** @var MappingProduct $mapping */
        $mapping = $this->getMappingInstance()->fetch();

        /* Action push data to Transcann*/
        if ($mapping) {
            $dataSend = $this->convertToTranscan()->toArray();


            $dataSend += $data;
            $api = new Items();
            if ($api->execute(['listItems' => [$dataSend]])) {
                $result = $api->getResponse()->getData();
                $transcannId = $result['result']['ResultOfItemsIntegration'][0]['XtentItemId'] ?? null;
                $transcannMetaId = $result['result']['ResultOfItemsIntegration'][0]['ItemCode'] ?? null;
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