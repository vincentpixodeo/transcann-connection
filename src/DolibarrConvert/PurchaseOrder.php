<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use DateTime;
use Exception;
use WMS\Xtent\Apis\QueryWebServices\GetReceptions;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Preparation;
use WMS\Xtent\Database\Builder\QueryBuilder;
use WMS\Xtent\Database\Builder\QueryJoinType;
use WMS\Xtent\DolibarrConvert\Pivots\MappingPurchaseOrder;

/**
 * dolibarr data 'fourn/class/fournisseur.commande.class.php'
 * $table llx_commande_fournisseur
 * @property int $rowid
 * @property string $ref
 * @property string $ref_ext
 * @property string $ref_supplier
 * @property int $fk_soc
 * @property int $fk_projet
 * @property int $fk_user_author
 * @property DateTime $date_valid
 * @property DateTime $date_approve
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
        return [
            'ref' => 'Order',
            'ref_supplier' => 'SupplierReference',
            'comment' => 'Comments'
        ];
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
        $mapping = $this->getMappingInstance();
        if ($mapping && $mapping->id() && $mapping->transcan_id) {
            $api = new GetReceptions();
            if ($api->execute(['filters' => "Id={$mapping->transcan_id}"])) {
                $data = $api->getResponse()->getData()['result'][0] ?? null;
                if ($data) {
                    $mapping->save(['transcan_payload' => json_encode($data)]);
                }
            } else {
                $errors = $api->getErrors();
                throw new TranscannSyncException(array_pop($errors), $api->getClient()->getLogs());
            }
        }
        return true;
    }

    protected function getEdiReceptionDetailsList(): array
    {
        $EdiReceptionDetailsList = [];
        $lines = PurchaseOrderLine::get(['fk_commande' => $this->id()], function (QueryBuilder $queryBuilder) {
            $queryBuilder->join('llx_product', 'fk_product', 'rowid')
                ->join('llx_product_fournisseur_price', 'llx_product.rowid', 'llx_product_fournisseur_price.fk_product', QueryJoinType::LeftJoin)
                ->join('llx_c_units', 'llx_product.fk_unit', 'llx_c_units.rowid', QueryJoinType::LeftJoin)
                ->join('llx_product_extrafields', 'llx_product.rowid', 'llx_product_extrafields.fk_object', QueryJoinType::LeftJoin)
                ->select([
                    'llx_commande_fournisseurdet.*',
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
                ]);
        });
        foreach ($lines as $index => $line) {

            $EdiReceptionDetailsList[] = [
                "BatchNumber" => null,
                "Comments" => null,
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
                "GrossWeight" => $line->llx_product_weight,
                "Id" => 0,
                "Inner" => null,
                "InternalItemId" => $line->fk_product,
                "ItemCode" => $line->ref,
                "LineNumber" => $index + 1,
                "MultiReferencePalletId" => null,
                "NetWeight" => null,
                "Outer" => null,
                "PackagingCode" => null,
                "Pallet" => "PAL0123456789",
                "PalletOccupationDepth" => null,
                "PalletOccupationHeight" => null,
                "PalletOccupationWidth" => null,
                "ParcelsPerLayer" => $line->llx_product_extrafields_cartonsparplan,
                "LayersPerPallet" => $line->llx_product_extrafields_cartonsparplan,
                "StatusCodeId" => null,
                "Value" => $line->subprice
            ];
        }
        return $EdiReceptionDetailsList;
    }

    function pushDataToTranscann(array $data = []): bool
    {
        return true;
        $this->fetch();

        try {
            QueryBuilder::begin();
            $reception = new Reception([
                'ref' => "(PROV{$this->id()})",
                'entity' => 1,
                'fk_soc' => $this->fk_soc,
                'fk_projet' => $this->fk_projet,
                'ref_ext' => $this->ref_ext,
                'ref_supplier' => $this->ref_supplier,
                'fk_user_author' => $this->fk_user_author,
                'fk_statut' => 0,
                'billed' => 0,
            ]);
            $reception->save();
            $reception->save(['ref' => "(PROV{$reception->id()})"]);
            $element = new ElementElement([
                'fk_source' => $this->id(),
                'sourcetype' => 'order_supplier',
                'fk_target' => $reception->id(),
                'targettype' => "reception",
            ]);

            $element->save();

            $reception->order = $this;

            $lines = PurchaseOrderLine::get(['fk_commande' => $this->id()]);
            foreach ($lines as $index => $line) {
                $line = new ReceptionLine([
                    'fk_commande' => $this->id(),
                    'fk_product' => $line->fk_product,
                    'fk_commandefourndet' => $line->id(),
                    'fk_projet' => $this->fk_projet,
                    'fk_reception' => $reception->id(),
                    'qty' => $line->qty,
                    'cost_price' => $line->subprice,
                    'status' => 0,
                    'comment' => 'Add by Auto',
                    'batch' => 'RECEPTION-LINE' . uniqid(),
                ]);
                $line->save();
            }
            QueryBuilder::commit();
        } catch (Exception $exception) {
            QueryBuilder::rollback();
            throw $exception;
        }

        return $reception->pushDataToTranscann();
    }

    public function getPrimaryKey(): string
    {
        return 'rowid';
    }

    protected static function boot(): void
    {
//        static::sqlEvent('init', function (QueryBuilder $queryBuilder) {
//            $orderTable = 'llx_commande_fournisseur';
//            $joinTable = 'llx_commande_fournisseur_dispatch';
//            $queryBuilder->select([
//                "{$orderTable}.*",
//                "{$joinTable}.batch",
//                "{$joinTable}.fk_product",
//                "{$joinTable}.fk_entrepot",
//                "{$joinTable}.qty",
//                "{$joinTable}.comment",
//            ]);
//
//            $queryBuilder->join($joinTable, 'rowid', 'fk_commande', QueryJoinType::LeftJoin);
//        });
    }
}