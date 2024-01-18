<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Contracts\AbstractObjectData;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Item;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataInterface;
use WMS\Xtent\DolibarrConvert\Contracts\ConvertTranscanInteface;
use WMS\Xtent\DolibarrConvert\Contracts\ConvertTranscanTrait;
use WMS\Xtent\DolibarrConvert\Contracts\DoSyncWithTranscannInterface;
use WMS\Xtent\DolibarrConvert\Contracts\DoSyncWithTranscannTrait;
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
class Product extends AbstractObjectData implements ConvertTranscanInteface, ObjectDataInterface, DoSyncWithTranscannInterface, CanSaveDataInterface
{
    use ConvertTranscanTrait;
    use DoSyncWithTranscannTrait;

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
//            "EdiItemGencode" => [
//                [
//                    "ItemGencod" => $this->barcode
//                ]
//            ]
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

    function pushDataToTranscann(array $data = []): bool
    {
        $this->fetch();

        /** @var MappingProduct $mapping */
        $mapping = $this->getMappingInstance()->fetch();

        /* Action push data to Transcann*/
        if ($mapping) {
            $dataSend = $this->convertToTranscan()->toArray();
            $api = new \WMS\Xtent\Apis\Item();
            $transcannId = $mapping->transcann_id ?? null;
            $dataSend += $data;
            if ($transcannId) {
                if ($api->put($transcannId, $dataSend)) {
                    $transcannInstance = new Item($api->getResponse()->getData());
                    $mapping->save([
                        'transcann_id' => $transcannInstance->Id,
                        'transcann_client_id' => $transcannInstance->ClientCodeId,
                        'transcan_meta_id' => $transcannInstance->_MetaId_,
                        'transcan_payload' => json_encode($transcannInstance->toArray())
                    ]);
                } else {
                    $errors = $api->getErrors();
                    throw new TranscannSyncException(array_pop($errors), $api->getClient()->getLogs());
                }
            } else {
                if ($api->create($dataSend)) {
                    $transcannInstance = new Item($api->getResponse()->getData());
                    $mapping->save([
                        'transcann_id' => $transcannInstance->Id,
                        'transcann_client_id' => $transcannInstance->ClientCodeId,
                        'transcan_meta_id' => $transcannInstance->_MetaId_,
                        'transcan_payload' => json_encode($transcannInstance->toArray())
                    ]);
                } else {
                    $errors = $api->getErrors();
                    dump($errors);
                    throw new TranscannSyncException(array_pop($errors), $api->getClient()->getLogs());
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