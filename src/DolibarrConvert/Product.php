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
            'llx_product_ref' => 'ItemCode',
            'llx_product_label' => 'Description',
            'llx_product_fournisseur_price_ref_fourn' => 'ExternalReference',
            // 'llx_product_tobatch' => 'BatchManagement',
            'llx_c_units_code' => 'UnitCode',
            // 'llx_c_units_label' => 'UnitLabel',
            'llx_product_extrafields_unitparcarton' => 'Outer',
            'llx_product_extrafields_cartonsparplan' => 'ParcelsPerLayer',
            'llx_product_extrafields_planpalette' => 'LayersPerPallet',
            'llx_product_weight' => 'SUGrossWeight',
            'llx_product_net_measure' => 'SUNetWeight',
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
                    "ItemGencod" => $this->llx_product_barcode ?? ''
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
                $apiFlow = new CheckFlowIntegrationStatus();
                sleep(5);
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

    protected static function boot(): void
    {
        static::sqlEvent('init', function (QueryBuilder $queryBuilder) {
            $queryBuilder->select([
                'llx_product.rowid as rowid',
                'llx_product.ref as llx_product_ref',
                'llx_product.label as llx_product_label',
                'llx_product_fournisseur_price.ref_fourn as llx_product_fournisseur_price_ref_fourn',
                'llx_product.tobatch as llx_product_tobatch',
                'llx_c_units.code as llx_c_units_code',
                'llx_c_units.label as llx_c_units_label',
                'llx_product_extrafields.unitparcarton as llx_product_extrafields_unitparcarton',
                'llx_product_extrafields.cartonsparplan as llx_product_extrafields_cartonsparplan',
                'llx_product_extrafields.planpalette as llx_product_extrafields_planpalette',
                'llx_product.weight as llx_product_weight',
                'llx_product.net_measure as llx_product_net_measure',
                'LEFT(llx_categorie.ref_ext, 2) as llx_categorie_ref_ext',
                'llx_societe.nom as llx_societe_nom',
                'llx_product.barcode as llx_product_barcode',
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