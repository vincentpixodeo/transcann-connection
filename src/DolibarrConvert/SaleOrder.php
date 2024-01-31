<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Apis\IntegrationWebServices\Preparations;
use WMS\Xtent\Apis\QueryWebServices\GetPreparations;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Preparation;
use WMS\Xtent\Database\Builder\QueryBuilder;
use WMS\Xtent\Database\Builder\QueryJoinType;
use WMS\Xtent\DolibarrConvert\Pivots\MappingSaleOrder;

/**
 * @property \DateTime $date_livraison
 * dolibarr data 'fourn/class/fournisseur.commande.class.php'
 * $table llx_commande
 */
class SaleOrder extends Model
{
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

    function getTranscanInstance(): string|ObjectDataInterface
    {
        return new Preparation();
    }

    public function getMainTable(): string
    {
        return 'commande';
    }

    function getAppendAttributes(): array
    {
        $order = [
            'ClientCodeId' => 2000,
            "ContactName" => $this->client_name,
            "ConsigneeAddress2" => null,
            "ConsigneeAddress3" => null,
            "ConsigneeAddress4" => null,
            "PreparationType" => "STD",
            "PlannedDeliveryDate" => $this->date_livraison?->format('YmdHi'),
        ];

        $EdiPreparationDetailsList = [];
        foreach ($this->lines() as $index => $line) {
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
                "InternalItemId" => $line->fk_product,
                "ItemCode" => $line->llx_product_ref,
                "LineNumber" => (string)($index + 1),
                "OrderedFullPallets" => null,
                "OrderedParcels" => 10,
                "OrderedSaleUnits" => null,
                "Pallet" => "00336600123456789",
                "Status" => null,
                "Support" => null
            ];
        }

        $order['EdiPreparationDetailsList'] = $EdiPreparationDetailsList;
        return $order;
    }

    function getMappingClass(): string
    {
        return MappingSaleOrder::class;
    }

    function updateDataFromTranscann(array $data = []): bool
    {
        $api = new GetPreparations();
        $mapping = $this->getMappingInstance();
        if ($mapping->transcan_id && $api->execute(['filters' => "Id={$mapping->transcan_id}"]) && $data = ($api->getResponse()->getData()['result'][0] ?? null)) {
            /** @var Preparation $preparation */
            $preparation = $this->getTranscanInstance();
            $preparation->setData($data);
            dd($preparation);
        }
        return true;
    }

    function lines(): \Generator
    {
        return SaleOrderLine::get(['fk_commande' => $this->id()], function (QueryBuilder $builder) {
            $builder->join('llx_product', 'fk_product', 'rowid')
                ->select([
                    'llx_commandedet.*',
                    'llx_product.ref as llx_product_ref',
                ]);
            $builder->where('fk_product is not null');
        });
    }

    function pushDataToTranscann(array $data = []): bool
    {
        $mapping = $this->getMappingInstance()->fetch();

        /* Action push data to Transcann*/
        if ($mapping) {

            $dataSend = $this->convertToTranscan()->toArray();

            $api = new Preparations();

            if ($api->execute(['listPreparations' => [$dataSend]])) {
                $result = $api->getResponse()->getData();
                $transcannId = $result['result']['ResultOfPreparationsIntegration'][0]['XtentPreparationId'] ?? null;
                $transcannMetaId = $result['result']['ResultOfPreparationsIntegration'][0]['OrderReference'] ?? null;
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
            $queryBuilder
                ->join('llx_societe', 'fk_soc', 'llx_societe.rowid')
                ->join('llx_c_country', 'fk_pays', 'rowid', QueryJoinType::LeftJoin, 'llx_societe')
                ->select([
                    'llx_commande.*',
                    'llx_societe.code_client as client_code',
                    'llx_societe.nom as client_name',
                    'llx_societe.phone as client_phone',
                    'llx_societe.fax as client_fax',
                    'llx_societe.email as client_email',
                    'llx_societe.address as client_address',
                    'llx_societe.town as client_city',
                    'llx_societe.zip as client_zip',
                    'llx_c_country.code as client_country',
                ]);
        });
    }
}