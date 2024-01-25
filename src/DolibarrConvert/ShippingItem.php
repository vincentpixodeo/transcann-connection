<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Item;
use WMS\Xtent\Database\Builder\QueryBuilder;

/**
 * @property int fk_expedition
 * @property int fk_origin_line
 * @property int fk_entrepot
 * @property float qty
 * @property int rang
 */
class ShippingItem extends Model
{
    public function getMainTable(): string
    {
        return 'expeditiondet';
    }

    public function getPrimaryKey(): string
    {
        return 'rowid';
    }

    function getMapAttributes(): array
    {
        return [];
    }

    function getAppendAttributes(): array
    {
        return [];
    }

    function getTranscanInstance(): string|ObjectDataInterface
    {
        return Item::class;
    }

    function updateDataFromTranscann(array $data = []): bool
    {
        return true;
    }

    function pushDataToTranscann(array $data = []): bool
    {
        return true;
    }

    function getMappingClass(): string
    {
        // TODO: Implement getMappingClass() method.
    }

    protected static function boot(): void
    {
        static::sqlEvent('init', function (QueryBuilder $query) {
            $query->select([
                "llx_expeditiondet.*",
                'llx_entrepot.ref as warehouse_ref',
                'llx_expeditiondet_batch.eatby as batch_eatby',
                'llx_expeditiondet_batch.sellby as batch_sellby',
                'llx_expeditiondet_batch.batch as batch_batch',
                'llx_expeditiondet_batch.fk_origin_stock as batch_fk_origin_stock',
                'llx_commandedet.fk_commande as product_fk_commande',
                'llx_commandedet.fk_product as product_fk_product',
                'llx_commandedet.label as product_label',
                'llx_product.ref  as product_ref',
            ])
                ->join('llx_entrepot', 'fk_entrepot', 'rowid')
                ->join('llx_expeditiondet_batch', 'rowid', 'fk_expeditiondet')
                ->join('llx_commandedet', 'fk_origin_line', 'rowid')
                ->join('llx_product', 'fk_product', 'rowid', null, 'llx_commandedet');
        });
    }
}