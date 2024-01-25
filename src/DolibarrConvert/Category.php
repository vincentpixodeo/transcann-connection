<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Address\Family;
use WMS\Xtent\DolibarrConvert\Pivots\MappingCategory;

/**
 * @property int rowid
 * @property int entity
 * @property int fk_parent
 * @property string label
 * @property string ref_ext
 * @property int type
 * @property string description
 * $table llx_categorie
 */
class Category extends Model
{

    function getMapAttributes(): array
    {
        return [
            'label' => 'Description',
        ];
    }

    function getTranscanInstance(): string|ObjectDataInterface
    {
        return Family::class;
    }

    public function getMainTable(): string
    {
        return 'llx_categorie';
    }

    function getMappingClass(): string
    {
        return MappingCategory::class;
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

    protected function defaultCondition(): array
    {
        return [
            'entity' => 1,
            'fk_parent' => 1
        ];
    }

    function getAppendAttributes(): array
    {
        return [];
    }
}