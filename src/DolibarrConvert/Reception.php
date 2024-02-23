<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use DateTime;
use Exception;
use Generator;
use WMS\Xtent\Apis\IntegrationWebServices\Receptions;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Enums\ReceptionStatus;
use WMS\Xtent\Data\Reception as TranscannReception;
use WMS\Xtent\Database\Builder\QueryBuilder;
use WMS\Xtent\Database\Builder\QueryJoinType;
use WMS\Xtent\DolibarrConvert\Pivots\MappingReception;
use WMS\Xtent\DolibarrConvert\Pivots\MappingWarehouse;
use WMS\Xtent\DolibarrConvert\Pivots\ModelPivot;

/**
 * @property int $rowid
 * @property string $ref
 * @property int $entity
 * @property int $fk_soc
 * @property int $fk_projet
 * @property int $ref_supplier
 * @property string $ref_ext
 * @property int $fk_statut
 * @property int $fk_user_author
 * @property int $billed
 * @property DateTime $date_delivery
 * @property DateTime $date_reception
 * @property string $tracking_number
 * @property int $weight_units
 * @property float $weight
 *
 * table element_element
 * @property int $fk_source
 * @property string $fk_sourcetype
 * dolibarr data 'fourn/class/fournisseur.commande.class.php'
 * $table llx_reception
 */
class Reception extends Model
{
    public ?PurchaseOrder $order = null;

    function getMapAttributes(): array
    {
        return [
        ];
    }

    function order(): ?PurchaseOrder
    {

        if (is_null($this->order)) {
            $this->order = PurchaseOrder::load($this->fk_source);
        }
        return $this->order;
    }

    protected function getEdiReceptionDetailsList(): array
    {
        $EdiReceptionDetailsList = [];
        $lines = PurchaseOrderLine::get(['fk_commande' => $this->order()->id()], function (QueryBuilder $queryBuilder) {
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

    function getTranscanInstance(): string|ObjectDataInterface
    {
        return new TranscannReception();
    }

    public function getMainTable(): string
    {
        return 'reception';
    }

    function getAppendAttributes(): array
    {
        $order = $this->order();
        return [
            "Order" => $order->ref,
            "SupplierReference" => $order->ref_supplier,
            "ClientCodeId" => 2000,
            "MovementCodeId" => "ENT",
            "AppointmentDate" => null,
            "ArrivalDate" => null,
            "DateOfPlannedReceiving" => $order->date_approve?->format('YmdHi'),
            "Comments" => "INTEGRE PAR API",
            "EdiReceptionDetailsList" => $this->getEdiReceptionDetailsList()
        ];

    }

    function getMappingClass(): string
    {
        return MappingReception::class;
    }

    function lines(callable $queryBuilderCallback = null): Generator
    {
        return ReceptionLine::get(['fk_reception' => $this->id()], function (QueryBuilder $queryBuilder) use ($queryBuilderCallback) {
            $queryBuilder->join('llx_product', 'fk_product', 'rowid');
            $queryBuilder->select([
                'llx_commande_fournisseur_dispatch.*',
                'llx_product.ref as fk_product_ref'
            ]);
            if ($queryBuilderCallback) {
                $queryBuilderCallback($queryBuilder);
            }
        });
    }

    function createFromTranscan($item): static
    {
        $instance = new static();
        $item instanceof \WMS\Xtent\Data\Reception || $item = new \WMS\Xtent\Data\Reception((array)$item);

        if ($item->StatusReception == ReceptionStatus::Validated->value) {
            $mapping = MappingReception::load($item->Id, 'transcan_id');

            if ($mapping && $mapping->fk_object_id) {
                $instance = static::load($mapping->fk_object_id);
            } else {
                $order = PurchaseOrder::load($item->Order, 'ref');
                $instance->order = $order;
                QueryBuilder::begin();
                $instance->setData([
                    'ref' => "(PROV-{$order->id()})",
                    'entity' => 1,
                    'fk_soc' => $order->fk_soc,
                    'fk_projet' => $order->fk_projet,
                    'ref_ext' => $order->ref_ext,
                    'ref_supplier' => $order->ref_supplier,
                    'fk_user_author' => $order->fk_user_author,
                    'fk_statut' => 0,
                    'billed' => 0,
                ]);
                $instance->save();

                $instance->save([
                    'ref' => "(RCP-{$instance->id()})",
                    'fk_statut' => 1
                ]);

                $element = new ElementElement([
                    'fk_source' => $order->id(),
                    'sourcetype' => 'order_supplier',
                    'fk_target' => $instance->id(),
                    'targettype' => "reception",
                ]);

                $element->save();

                QueryBuilder::commit();
                $instance->getMappingInstance([
                    ModelPivot::PROPERTY_TRANSCAN_ID => $item->Id,
                    ModelPivot::PROPERTY_TRANSCAN_META_ID => $item->Order,
                    ModelPivot::PROPERTY_TRANSCAN_PAYLOAD => json_encode($item->toArray()),
                    ModelPivot::PROPERTY_TRANSCAN_INTEGRATE_STATUS => ModelPivot::INTEGRATE_STATUS_OK,
                ])->save();
            }
            $instance->updateDataFromTranscann($item->toArray());
            return $instance;
        } else {
            throw new Exception('status-->' . $item->StatusReception);
        }

    }


    /**
     * @param array $data Data of Reception Transcan
     * @return bool
     * @throws Exception
     */

    function updateDataFromTranscann(array $data = []): bool
    {
        $transcanReception = new \WMS\Xtent\Data\Reception($data);

        $mapping = MappingReception::load($transcanReception->Id, 'transcan_id');

        if ($mapping && $this->id() == $mapping->fk_object_id && $order = $this->order()) {

            $this->setMappingInstance($mapping);

            $mappingWarehouse = MappingWarehouse::load($transcanReception->Warehouse, 'transcan_id');

            if ($transcanReception->StatusReception == ReceptionStatus::Validated->value) {
                QueryBuilder::begin();
                foreach ($transcanReception->StocksList as $stock) {

                    $orderLine = PurchaseOrderLine::load($stock->ItemCode, 'llx_product.ref', function (QueryBuilder $queryBuilder) {
                        $queryBuilder->join('llx_product', 'fk_product', 'rowid')
                            ->select(['llx_commande_fournisseurdet.*']);
                    });

                    if ($orderLine) {
                        $line = ReceptionLine::load($order->id(), 'fk_commande', function (QueryBuilder $queryBuilder) use ($orderLine, $stock) {
                            $queryBuilder->where([
                                ['fk_reception', $this->id()],
                                ['fk_product', $orderLine->fk_product],
                                ['fk_commandefourndet', $orderLine->id()],
                                ['batch', $stock->BatchNumber],
                            ]);
                        });

                        $dataUpdate = [
                            'fk_commande' => $order->id(),
                            'fk_product' => $orderLine->fk_product,
                            'fk_commandefourndet' => $orderLine->id(),
                            'fk_projet' => $order->fk_projet,
                            'fk_reception' => $this->id(),
                            'qty' => $stock->SalesUnit,
                            'cost_price' => $orderLine->subprice,
                            'status' => 1,
                            'comment' => 'TRANSCAN StocksList ID ' . $stock->Id,
                            'batch' => $stock->BatchNumber,
                            'eatby' => $stock->ExpiryDate,
                            'fk_entrepot' => $mappingWarehouse?->fk_object_id,
                        ];
                        if (!$line) {
                            $line = new ReceptionLine($dataUpdate);
                        } else {
                            $line->addData($dataUpdate);
                        }

                        $line->save();
                    }
                }
                $this->save([
                    'fk_statut' => 1
                ]);
                QueryBuilder::commit();
                $mapping->save([
                    ModelPivot::PROPERTY_TRANSCAN_META_ID => $transcanReception->Order,
                    ModelPivot::PROPERTY_TRANSCAN_PAYLOAD => json_encode($transcanReception->toArray()),
                    ModelPivot::PROPERTY_TRANSCAN_INTEGRATE_STATUS => ModelPivot::INTEGRATE_STATUS_OK,
                ]);
                return true;
            } else {
                throw new Exception('status-->' . $transcanReception->StatusReception);
            }
        }
        return false;
    }

    function pushDataToTranscann(array $data = []): null|false|\WMS\Xtent\Http\Log
    {
        $this->fetch();
        $mapping = $this->getMappingInstance()->fetch();
        $dataSend = $this->convertToTranscan()->toArray();
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
            $mainTable = $queryBuilder->getTable();
            $table = getDbPrefix() . 'element_element';
            $queryBuilder->joinWhere($table . ' as el', [['el.fk_target = ' . $mainTable . '.rowid'], ['el.targettype = "reception"']]);
            $queryBuilder->select([
                $mainTable . '.*',
                'el.fk_source',
                'el.sourcetype',
            ]);
        });
    }
}