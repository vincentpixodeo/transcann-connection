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
use WMS\Xtent\Database\Builder\QueryBuilder;
use WMS\Xtent\Database\Builder\QueryJoinType;
use WMS\Xtent\DolibarrConvert\Pivots\MappingProduct;
use WMS\Xtent\Http\Log;

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
            'ref' => 'ItemCode',
            'label' => 'Description',
            'llx_product_fournisseur_price_ref_fourn' => 'ExternalReference',
            // 'llx_product_tobatch' => 'BatchManagement',
            'llx_c_units_code' => 'UnitCode',
            // 'llx_c_units_label' => 'UnitLabel',
            'llx_product_extrafields_unitparcarton' => 'Outer',
            'llx_product_extrafields_cartonsparplan' => 'ParcelsPerLayer',
            'llx_product_extrafields_planpalette' => 'LayersPerPallet',
            'weight' => 'SUGrossWeight',
            'net_measure' => 'SUNetWeight',
            'llx_categorie_ref_ext' => 'FamilyCode',
            'llx_societe_nom' => 'CustomizedField1',
//            'llx_product_barcode' => 'ItemGencod',
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
//        $extraField = $this->fetchExtraFields();
        return [
            'ClientCodeId' => 2000,
            "ExternalReference" => null,
            "BatchManagement" => "L",
            "RotationCode" => "B",
//            "UnitCode" => $extraField?->contenance,
            "Comments" => null,
            "Inner" => 1,
//            "Outer" => $extraField?->unitecarton,
//            "ParcelsPerLayer" => $extraField?->cartonplan,
//            "LayersPerPallet" => $extraField?->planpalette,
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
//            "SUGrossWeight" => $extraField?->poidsbrut,
//            "SUNetWeight" => $extraField?->poidsnet,
            "CurrencyId" => "EUR",
            "PackagingCode" => "EUR",
            "InboundStatus" => null,
//            "ReturnStatus" => "IND",
            "OutboundStatus" => null,
//            "SupplierCodeId" => 111,
//            "FamilyCode" => "AAA",
            "EdiItemGencode" => [
                [
                    "ItemGencod" => $this->barcode ?? ''
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

    public function convertToTranscan(): ObjectDataInterface
    {
        $instance = parent::convertToTranscan();
        if ($instance->Outer == 0) {
            $instance->Outer = 1;
        }
        if ($instance->LayersPerPallet == 0) {
            $instance->LayersPerPallet = 1;
        }
        if ($instance->ParcelsPerLayer == 0) {
            $instance->ParcelsPerLayer = 1;
        }
        return $instance;
    }

    /**
     * @throws TranscannSyncException
     */
    function pushDataToTranscann(array $data = []): Log
    {
        $this->fetch();
        /** @var MappingProduct $mapping */
        $mapping = $this->getMappingInstance()->fetch();
        /* Action push data to Transcann*/
        if ($mapping) {

            $dataSend = $this->convertToTranscan()->toArray();

            $dataSend = array_merge($dataSend, $data);

            $api = new Items();
            if ($api->execute(['listItems' => [$dataSend]])) {
                $result = $api->getResponse()->getData();
                $transcannId = $result['result']['ResultOfItemsIntegration'][0]['XtentItemId'] ?? null;
                $transcannMetaId = $result['result']['ResultOfItemsIntegration'][0]['ItemCode'] ?? null;
                $folowId = $result['result']['FlowsId'][0]['FlowID'] ?? null;
                $mapping->transcan_id = $transcannId;
                $mapping->transcan_meta_id = $transcannMetaId;
                $mapping->transcan_payload = json_encode($result['result']['FlowsId']);
                $mapping->save();
                $apiFlow = new CheckFlowIntegrationStatus();
                if ($apiFlow->execute($folowId)) {

                    $checkResult = $apiFlow->getResponse()->getData();
                    $mapping->transcan_payload = json_encode($checkResult);
                    $mapping->save();
                } else {
                    $errors = $apiFlow->getErrors();
                    throw new TranscannSyncException(array_pop($errors), $apiFlow->getClient()->getLogs());
                }
                return $api->getClient()->getCurrentLog();
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

    protected static function boot(): void
    {
        static::sqlEvent('init', function (QueryBuilder $queryBuilder) {
            $queryBuilder->select([
                'llx_product.rowid as rowid',
                'llx_product.ref',
                'llx_product.label',
                'llx_product.tobatch',
                'llx_product.weight',
                'llx_product.net_measure',
                'llx_product.barcode',
                'llx_product_fournisseur_price.ref_fourn as llx_product_fournisseur_price_ref_fourn',
                'llx_c_units.code as llx_c_units_code',
                'llx_c_units.label as llx_c_units_label',
                'llx_product_extrafields.unitparcarton as llx_product_extrafields_unitparcarton',
                'llx_product_extrafields.cartonsparplan as llx_product_extrafields_cartonsparplan',
                'llx_product_extrafields.planpalette as llx_product_extrafields_planpalette',
                'LEFT(llx_categorie.ref_ext, 2) as llx_categorie_ref_ext',
                'llx_societe.nom as llx_societe_nom',
            ]);
            $queryBuilder->join('llx_product_fournisseur_price', 'llx_product.rowid', 'llx_product_fournisseur_price.fk_product', QueryJoinType::LeftJoin)
                ->join('llx_c_units', 'llx_product.fk_unit', 'llx_c_units.rowid', QueryJoinType::LeftJoin)
                ->join('llx_product_extrafields', 'llx_product.rowid', 'llx_product_extrafields.fk_object', QueryJoinType::LeftJoin)
                ->join('llx_categorie_product', 'llx_product.rowid', 'llx_categorie_product.fk_product', QueryJoinType::LeftJoin)
                ->join('llx_categorie', 'llx_categorie_product.fk_categorie', 'llx_categorie.rowid', QueryJoinType::LeftJoin)
                ->join('llx_societe', 'llx_product_fournisseur_price.fk_soc', 'llx_societe.rowid', QueryJoinType::LeftJoin)/*->where(['llx_product.tosell' => 1, 'llx_product.tobuy'])*/
            ;

            $queryBuilder->where([
                ['llx_product.tosell', 1],
                ['llx_product.tobuy', 1]
            ]);
        });
    }

}