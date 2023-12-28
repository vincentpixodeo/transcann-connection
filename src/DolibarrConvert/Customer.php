<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Contracts\AbstractObjectData;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Client;
use WMS\Xtent\DolibarrConvert\Contracts\ConvertTranscanInteface;
use WMS\Xtent\DolibarrConvert\Contracts\ConvertTranscanTrait;
use WMS\Xtent\DolibarrConvert\Contracts\DoSyncWithTranscannByLogTrait;
use WMS\Xtent\DolibarrConvert\Contracts\DoSyncWithTranscannInterface;

/**
 * @property int rowid
 * @property string ref
 * @property string label
 * @property string description
 * @property string price
 * $table llx_societe
 */
class Customer extends AbstractObjectData implements ConvertTranscanInteface, ObjectDataInterface, DoSyncWithTranscannInterface
{
    use ConvertTranscanTrait;
    use DoSyncWithTranscannByLogTrait;

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
        return 'customers';
    }
}