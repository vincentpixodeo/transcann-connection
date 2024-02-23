<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use DateTime;
use Exception;
use Generator;
use WMS\Xtent\Apis\IntegrationWebServices\Preparations as IntegrationWebServices_Preparations;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Enums\OrderStatus;
use WMS\Xtent\Data\Preparation;
use WMS\Xtent\Database\Builder\QueryBuilder;
use WMS\Xtent\Database\Builder\QueryJoinType;
use WMS\Xtent\DolibarrConvert\Pivots\MappingShipping;
use WMS\Xtent\DolibarrConvert\Pivots\ModelPivot;

/**
 * @property string ref
 * @property string entity
 * @property int fk_soc
 * @property int fk_project
 * @property string ref_ext
 * @property string ref_customer
 * @property DateTime date_creation
 * @property int fk_user_author
 * @property int fk_user_modif
 * @property DateTime date_valid
 * @property int fk_user_valid
 * @property DateTime date_delivery
 * @property DateTime date_expedition
 * @property int fk_statut
 * @property int billed
 * @property float height
 * @property float width
 * @property int size_units
 * @property float size
 * @property int weight_units
 * @property float weight
 */
class Shipping extends Model
{
    protected ?SaleOrder $order = null;

    function order(): ?SaleOrder
    {

        if (is_null($this->order)) {
            $this->order = SaleOrder::load($this->fk_source);
        }
        return $this->order;
    }

    /**
     * @return Generator{ShippingItem}|null
     */
    function lines(): ?Generator
    {
        try {
            return (new ShippingItem())->list(['fk_expedition' => $this->id()]);
        } catch (Exception $exception) {
            dump($exception);
            return null;
        }
    }

    function contact()
    {
        $contact = new StaticModel('element_contact');

        return $contact->fetch($this->order()->id(), 'element_id', function (QueryBuilder $queryBuilder) {
            $queryBuilder->join('llx_c_type_contact', 'fk_c_type_contact', 'rowid');
            $queryBuilder->join('llx_socpeople', 'fk_socpeople', 'rowid');
            $queryBuilder->where([['llx_c_type_contact.source', 'external'], ['llx_c_type_contact.active', 1], ['llx_c_type_contact.element', 'commande']]);
            $queryBuilder->select(['llx_socpeople.*']);
        });
    }

    public function getMainTable(): string
    {
        return 'expedition';
    }


    public function getPrimaryKey(): string
    {
        return 'rowid';
    }

    function getMapAttributes(): array
    {
        return [
            'ref' => 'Order',
            'client_code' => "ConsigneeReference",
            'client_name' => "ConsigneeName",
            'client_zip' => "ConsigneeZipCode",
            'client_city' => "ConsigneeCityId",
            'client_email' => "ContactMail",
            'client_address' => "ConsigneeAddress1",
            'client_country' => "ConsigneeCountryId",
            'client_phone' => "ContactPhone"
        ];
    }

    function getAppendAttributes(): array
    {
        return [
            'ClientCodeId' => 2000
        ];
    }

    function getTranscanInstance(): string|ObjectDataInterface
    {
        return Preparation::class;
    }

    function createFromTranscan($item): static
    {
        $item instanceof Preparation || $item = new Preparation($item);
        $mapping = MappingShipping::load($item->Id, ModelPivot::PROPERTY_TRANSCAN_ID);
        if (!$mapping) {
            $mapping = new MappingShipping();
        }
        $mapping->save([
            ModelPivot::PROPERTY_TRANSCAN_ID => $item->Id,
            ModelPivot::PROPERTY_TRANSCAN_META_ID => $item->Order,
            ModelPivot::PROPERTY_TRANSCAN_PAYLOAD => json_encode($item->toArray()),
            ModelPivot::PROPERTY_TRANSCAN_INTEGRATE_STATUS => ModelPivot::INTEGRATE_STATUS_OK,
        ]);
        if ($mapping->fk_object_id) {
            $instance = static::load($mapping->fk_object_id);
            $instance->setMappingInstance($mapping);
            $instance->updateDataFromTranscann($item->toArray());
            return $instance;
        }
        return $this;
    }

    function updateDataFromTranscann(array $data = [])
    {
        $preparation = new Preparation($data);
        $mapping = $this->getMappingInstance();

        if ($this->id() && $mapping && $this->id() == $mapping->fk_object_id && $preparation->OrderStatus == OrderStatus::Validated->value) {
            QueryBuilder::begin();

            foreach ($preparation->StocksList as $stock) {
                $stock->BatchNumber = uniqid();

                $orderLine = ShippingItem::load($stock->ItemCode, 'llx_product.ref');

                if ($orderLine) {
                    $batch = ShippingBatch::load($orderLine->id(), 'fk_expeditiondet', function (QueryBuilder $queryBuilder) use ($stock) {
                        $queryBuilder->where('batch', $stock->BatchNumber);
                    });

                    is_null($batch) && $batch = new ShippingBatch([
                        'fk_expeditiondet' => $orderLine->id()
                    ]);
                    $batch->save([
                        'qty' => $stock->SalesUnitAbs,
                        'batch' => $stock->BatchNumber,
                        'eatby' => $stock->ExpiryDate,
                        'fk_origin_stock' => $orderLine->batch_fk_origin_stock,
                    ]);
                }
            }

            QueryBuilder::commit();
            $mapping->save([
                ModelPivot::PROPERTY_TRANSCAN_META_ID => $preparation->Order,
                ModelPivot::PROPERTY_TRANSCAN_PAYLOAD => json_encode($preparation->toArray()),
                ModelPivot::PROPERTY_TRANSCAN_INTEGRATE_STATUS => ModelPivot::INTEGRATE_STATUS_OK,
            ]);

            $this->save(['fk_statut' => 1]);
            return true;
        }
    }

    function pushDataToTranscann(array $data = []): mixed
    {
        $this->fetch();

        $this->addData($data);
        $mapping = $this->getMappingInstance()->fetch();

        $lines = $this->lines();

        $dataSend = $this->getDataSendToTranscan($lines);

        $api = new IntegrationWebServices_Preparations();

        if ($api->execute($dataSend)) {
            $result = $api->getResponse()->getData();
            $transcannId = $result['result']['ResultOfPreparationsIntegration'][0]['XtentPreparationId'] ?? null;
            $transcannMetaId = $result['result']['ResultOfPreparationsIntegration'][0]['OrderReference'] ?? null;
            $mapping->transcan_id = $transcannId;
            $mapping->transcan_meta_id = $transcannMetaId;
            $mapping->transcan_payload = json_encode($result['result']['FlowsId']);
            $mapping->save();
            return $api->getClient()->getCurrentLog();
        } else {
            $errors = $api->getErrors();
            throw new TranscannSyncException(array_pop($errors), $api->getClient()->getLogs());
        }
        return true;
    }

    protected function getDataSendToTranscan(Generator $lines): array
    {
        $dataSend = $this->convertToTranscan()->toArray();

        $dataSend = array_merge($dataSend, [
            "ClientReference" => $this->client_code,
            "ContactName" => $this->client_name,
            "ConsigneeReference" => "REF_DEST_126",
            "PreparationType" => "STD",
            "PlannedDeliveryDate" => $this->date_delivery?->format('YmdHi'),
            "ConsigneeAddress1" => $this->client_address,
            "ConsigneeAddress2" => null,
            "ConsigneeAddress3" => null,
            "ConsigneeAddress4" => null,
        ]);

        if ($contact = $this->contact()) {
            if ($contact->email) {
                $dataSend['ContactMail'] = $contact->email;
            }
            if ($contact->phone) {
                $dataSend['ContactPhone'] = $contact->phone;
            }
            if ($contact->lastname) {
                $dataSend['ContactName'] = $contact->lastname;
            }
        }

        $EdiPreparationDetailsList = [];

        foreach ($lines as $index => $line) {
            $EdiPreparationDetailsList[] = [
                "BatchNumber" => $line->batch_batch,
                "Comments" => null,
                "CRD" => null,
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
                "ExpiryDate" => null,
                "InternalItemId" => $line->product_ref,
                "ItemCode" => $line->product_ref,
                "LineNumber" => (string)($index + 1),
                "OrderedFullPallets" => null,
                "OrderedParcels" => 10,
                "OrderedSaleUnits" => $line->qty,
                "Pallet" => "00336600123456789",
                "Status" => null,
                "Support" => null
            ];
        }
        $dataSend['EdiPreparationDetailsList'] = $EdiPreparationDetailsList;

        return ['listPreparations' => [$dataSend]];
    }

    function getMappingClass(): string
    {
        return MappingShipping::class;
    }


    protected static function boot(): void
    {
        static::sqlEvent('init', function (QueryBuilder $builder) {
            $mainTable = $builder->getTable();
            $table = getDbPrefix() . 'element_element';
            $builder->joinWhere($table . ' as el', [['el.fk_target = ' . $mainTable . '.rowid'], ['el.targettype = "shipping"']]);
            $builder->select([
                $mainTable . '.*',
                'el.fk_source',
                'el.sourcetype',
            ]);
            $builder
                ->select([
                    'llx_expedition.*',
                    'llx_societe.code_client as client_code',
                    'llx_societe.nom as client_name',
                    'llx_societe.phone as client_phone',
                    'llx_societe.fax as client_fax',
                    'llx_societe.email as client_email',
                    'llx_societe.address as client_address',
                    'llx_societe.town as client_city',
                    'llx_societe.zip as client_zip',
                    'llx_c_country.code as client_country',
                ])
                ->join('llx_societe', 'fk_soc', 'rowid')
                ->join('llx_c_country', 'fk_pays', 'rowid', QueryJoinType::LeftJoin, 'llx_societe');
        });
    }

}