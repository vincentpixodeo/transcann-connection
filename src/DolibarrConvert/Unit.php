<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Unit as TranscanUnit;
use WMS\Xtent\DolibarrConvert\Pivots\MappingUnit;

/**
 * @property int rowid
 * @property string code
 * @property int sortorder
 * @property string scale
 * @property string label
 * @property string short_label
 * @property string unit_type
 * @property int active
 * $table llx_categorie
 */
class Unit extends Model
{

    function getMapAttributes(): array
    {
        return [
            'label' => 'Description',
        ];
    }

    function getTranscanInstance(): string|ObjectDataInterface
    {
        return TranscanUnit::class;
    }

    public function getMainTable(): string
    {
        return 'c_units';
    }

    function getMappingClass(): string
    {
        return MappingUnit::class;
    }

    function updateDataFromTranscann(array $data = []): bool
    {
        return true;
    }

    function pushDataToTranscann(array $data = []): bool
    {

    }

    public function getPrimaryKey(): string
    {
        return 'rowid';
    }


    function getAppendAttributes(): array
    {
        return [];
    }
}