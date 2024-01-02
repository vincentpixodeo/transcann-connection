<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Contracts\AbstractObjectData;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Item;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataInterface;
use WMS\Xtent\DolibarrConvert\Contracts\ConvertTranscanInteface;
use WMS\Xtent\DolibarrConvert\Contracts\ConvertTranscanTrait;
use WMS\Xtent\DolibarrConvert\Contracts\DoSyncWithTranscannByLogTrait;
use WMS\Xtent\DolibarrConvert\Contracts\DoSyncWithTranscannInterface;
use WMS\Xtent\DolibarrConvert\Pivots\MappingProduct;

/**
 * @property int rowid
 * @property string ref
 * @property string label
 * @property string description
 * @property string price
 * $table llx_product
 */
class Product extends AbstractObjectData implements ConvertTranscanInteface, ObjectDataInterface, DoSyncWithTranscannInterface, CanSaveDataInterface
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
        return Item::class;
    }

    protected function getMainTable(): string
    {
        return 'products';
    }

    function getMappingClass(): string
    {
        return MappingProduct::class;
    }

    function getAppendAttributes(): array
    {
        return ['ClientCodeId' => 2000];
    }
}