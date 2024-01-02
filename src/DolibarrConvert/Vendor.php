<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Contracts\AbstractObjectData;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Client;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataByLogTrait;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataInterface;
use WMS\Xtent\DolibarrConvert\Contracts\ConvertTranscanInteface;
use WMS\Xtent\DolibarrConvert\Contracts\ConvertTranscanTrait;
use WMS\Xtent\DolibarrConvert\Contracts\DoSyncWithTranscannByLogTrait;
use WMS\Xtent\DolibarrConvert\Contracts\DoSyncWithTranscannInterface;
use WMS\Xtent\DolibarrConvert\Pivots\MappingVendor;

/**
 * @property int rowid
 * @property string ref
 * @property string label
 * @property string description
 * @property string price
 * $table llx_societe
 */
class Vendor extends AbstractObjectData implements ConvertTranscanInteface, ObjectDataInterface, DoSyncWithTranscannInterface, CanSaveDataInterface
{
    use ConvertTranscanTrait;
    use DoSyncWithTranscannByLogTrait;
    use CanSaveDataByLogTrait;

    function getMapAttributes(): array
    {
        return [
            'label' => 'Description',
            'ref' => 'ItemCode',
            'price' => 'Value'
        ];
    }

    function getTranscanInstance(): string|ObjectDataInterface
    {
        return Client::class;
    }

    protected function getMainTable(): string
    {
        return 'vendors';
    }

    function getMappingClass(): string
    {
        return MappingVendor::class;
    }

    function getAppendAttributes(): array
    {
        return [
            'WarehousePartyCategory' => 1
        ];
    }
}