<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Contracts\AbstractObjectData;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\QuantityItem;
use WMS\Xtent\Database\Builder\QueryBuilder;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataByDatabaseTrait;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataInterface;
use WMS\Xtent\WmsXtentService;

/**
 * @property string batch
 * @property int fk_product
 * @property int fk_entrepot
 * @property int value
 * @property float price
 * @property int type_mouvement
 * @property string label
 * @property int fk_user_author
 * @property int fk_origin
 */
class StockMovement extends AbstractObjectData implements ObjectDataInterface, CanSaveDataInterface
{
    const DEFAULT_WAREHOUSE_ID = 2;
    use CanSaveDataByDatabaseTrait;

    public function getMainTable(): string
    {
        return 'stock_mouvement';
    }

    public function getPrimaryKey(): string
    {
        return 'rowid';
    }

    function createFromTranscan($data)
    {
        $stockItem = new QuantityItem($data);

        if (!$stockItem->BatchNumber) {
            throw new \Exception('BatchNumber is empty');
        }

        $instance = static::load($stockItem->BatchNumber, 'batch', function (QueryBuilder $queryBuilder) {
            $queryBuilder->join('llx_product', 'fk_product', 'rowid');
            $queryBuilder->select(['llx_stock_mouvement.*']);
        });


        $product = Product::load($stockItem->ItemCode, 'ref');

        if (!$product)
            throw new \Exception('product not found');

        $currentStock = static::load($product->id(), 'fk_product', function (QueryBuilder $queryBuilder) {
            $queryBuilder->where('fk_entrepot', self::DEFAULT_WAREHOUSE_ID);
            $queryBuilder->select(['SUM(value) as total']);
        });

        $productStock = (new StaticModel('llx_product_stock', 'rowid'))->fetch($product->id(), 'fk_product', fn(QueryBuilder $queryBuilder) => $queryBuilder->where('fk_entrepot', self::DEFAULT_WAREHOUSE_ID));
        $currentQuantity = $currentStock->total ?? 0;
        $realQuantity = $stockItem->SURealStock;

        $nextQuantity = $realQuantity - $currentQuantity;

        if ($nextQuantity == 0) {
            throw new \Exception('Quantity not change');
        }
        if (!$productStock) {
            $productStock = (new StaticModel('llx_product_stock', 'rowid'));
            $productStock->addData([
                'fk_entrepot' => self::DEFAULT_WAREHOUSE_ID,
                'fk_product' => $product->id()
            ]);
        }
        if (!$instance) {
            $instance = new static();
        }
        $authId = WmsXtentService::config('default_auth_id');

        QueryBuilder::begin();
        try {
            $productStock->save([
                'reel' => $realQuantity
            ]);

            $instance->save([
                'batch' => $stockItem->BatchNumber,
                'fk_product' => $product->id(),
                'fk_entrepot' => 2,
                'value' => (float)$nextQuantity,
                'price' => 0,
                'type_mouvement' => $nextQuantity > 0 ? 0 : -1,
                'label' => 'Update via Transcan',
                'fk_user_author' => $authId,
                'fk_origin' => 0
            ]);

            $productBatch = (new StaticModel('llx_product_batch', 'rowid'))->fetch($stockItem->BatchNumber, 'batch', fn(QueryBuilder $dt) => $dt->where('fk_product_stock', $productStock->id()));
            if (!$productBatch) {
                $productBatch = (new StaticModel('llx_product_batch', 'rowid'));
            }
            $productBatch->save([
                'fk_product_stock' => $productStock->id(),
                'batch' => $stockItem->BatchNumber,
                'qty' => $nextQuantity,
                'import_key' => null
            ]);

            QueryBuilder::commit();
            return $instance;
        } catch (\Exception $exception) {
            QueryBuilder::rollback();
            throw $exception;
        }
    }

}