<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use DateTime;
use WMS\Xtent\Apis\IntegrationWebServices\Receptions;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Preparation;
use WMS\Xtent\Database\Builder\QueryBuilder;
use WMS\Xtent\Database\Builder\QueryJoinType;
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
                "BatchNumber" => $this->batch,
                "Comments" => $this->comment,
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
        $dataSend = [
            "Order" => $this->ref,
            "SupplierReference" => $this->ref_supplier,
            "ClientCodeId" => 2000,
            "MovementCodeId" => "ENT",
            "AppointmentDate" => null,
            "ArrivalDate" => $this->date_valid?->format('YmdHi'),
            "DateOfPlannedReceiving" => $this->date_approve?->format('YmdHi'),
            "Comments" => "INTEGRE PAR API"
        ];


        $dataSend['EdiReceptionDetailsList'] = $this->getEdiReceptionDetailsList();
        $mapping = $this->getMappingInstance()->fetch();
    
        if ($mapping) {
            $api = new Receptions();
            if ($api->execute(['listReceptions' => [$dataSend]])) {
                $result = $api->getResponse()->getData();
                $transcannId = $result['result']['ResultOfReceptionsIntegration'][0]['XtentReceptionId'] ?? null;
                $transcannMetaId = $result['result']['ResultOfReceptionsIntegration'][0]['OrderReference'] ?? null;
                $folowId = $result['result']['FlowsId'][0]['FlowID'] ?? null;
                $mapping->transcan_id = $transcannId;
                $mapping->transcan_meta_id = $transcannMetaId;
                $mapping->transcan_payload = json_encode($result['result']['FlowsId']);
                $mapping->save();
                return true;
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
            $orderTable = 'llx_commande_fournisseur';
            $joinTable = 'llx_commande_fournisseur_dispatch';
            $queryBuilder->select([
                "{$orderTable}.*",
                "{$joinTable}.batch",
                "{$joinTable}.fk_product",
                "{$joinTable}.fk_entrepot",
                "{$joinTable}.qty",
                "{$joinTable}.comment",
            ]);

            $queryBuilder->join($joinTable, 'rowid', 'fk_commande', QueryJoinType::LeftJoin);
        });
    }
}