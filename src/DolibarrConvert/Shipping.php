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
use WMS\Xtent\Apis\QueryWebServices\CheckFlowIntegrationStatus;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Preparation;
use WMS\Xtent\Database\Builder\QueryBuilder;
use WMS\Xtent\Database\Builder\QueryJoinType;
use WMS\Xtent\DolibarrConvert\Pivots\MappingShipping;

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
 * @property int fk_status
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
            'ref' => 'Order'
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

    function updateDataFromTranscann(array $data = []): bool
    {
        return true;
    }

    protected static function boot(): void
    {
        static::sqlEvent('init', function (QueryBuilder $builder) {
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

    function pushDataToTranscann(array $data = []): bool
    {

        $this->addData($data);
        $mapping = $this->getMappingInstance()->fetch();

        $lines = $this->lines();

        $dataSend = $this->getDataSendToTranscan($lines);
        $api = new IntegrationWebServices_Preparations();

        if ($api->execute($dataSend)) {
            $result = $api->getResponse()->getData();
            $transcannId = $result['result']['ResultOfPreparationsIntegration'][0]['XtentPreparationId'] ?? null;
            $transcannMetaId = $result['result']['ResultOfPreparationsIntegration'][0]['SupplierReference'] ?? null;
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
            dd($api->getClient()->getLogs());
        }
        return true;
    }

    function getDataSendToTranscan(Generator $lines): array
    {
        $dataSend = $this->convertToTranscan()->toArray();

        $dataSend = $dataSend + [
                "ClientReference" => $this->client_code,
                "ConsigneeReference" => "REF_DEST_126",
                "PreparationType" => "STD",
                "PlannedDeliveryDate" => $this->date_delivery?->format('YmdHi'),
                "ConsigneeAddress1" => $this->client_address,
                "ConsigneeAddress2" => null,
                "ConsigneeAddress3" => null,
                "ConsigneeAddress4" => null,
                "ConsigneeCountryId" => $this->client_country,
                "ConsigneeName" => $this->client_name,
                "ConsigneeZipCode" => $this->client_zip,
                "ConsigneeCityId" => $this->client_city,
                "ContactMail" => $this->client_email,
                "ContactName" => $this->client_name,
                "ContactPhone" => $this->client_phone,
            ];

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
                "OrderedSaleUnits" => null,
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
}