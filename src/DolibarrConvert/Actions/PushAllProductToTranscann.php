<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Actions;

use WMS\Xtent\Apis\IntegrationWebServices\Items;
use WMS\Xtent\Database\Builder\QueryBuilder;
use WMS\Xtent\Database\Builder\QueryConditionType;
use WMS\Xtent\Database\Builder\QueryJoinType;
use WMS\Xtent\DolibarrConvert\Product;
use WMS\Xtent\DolibarrConvert\TranscannSyncException;

class PushAllProductToTranscann
{
    const NUM_LINES = 100;

    public array $dataSend = [];
    public array $mapItemCode = [];

    /**
     * @throws TranscannSyncException
     */
    function execute($executeNow = false)
    {

        /** @var Product $product */
        foreach (Product::get([], function (QueryBuilder $queryBuilder) {
            $queryBuilder->join('llx_transcannconnection_mapping_products', 'rowid', 'fk_object_id', QueryJoinType::LeftJoin);
            $queryBuilder->where([['llx_transcannconnection_mapping_products.transcan_integrate_status <>', 1], ['llx_transcannconnection_mapping_products.transcan_integrate_status', null, null, QueryConditionType::OR]]);
            $queryBuilder->select(['llx_transcannconnection_mapping_products.transcan_integrate_status']);
        }) as $product) {
      
            $this->mapItemCode[$product->ref] = $product->rowid;
            $this->dataSend[] = $product->convertToTranscan()->toArray();
            if (count($this->dataSend) == self::NUM_LINES) {
                addAction([self::class, 'pushData'], ['listItems' => $this->dataSend, 'mapItemCode' => $this->mapItemCode], $executeNow);
                $this->dataSend = [];
                $this->mapItemCode = [];
            }
        }

        addAction([self::class, 'pushData'], ['listItems' => $this->dataSend, 'mapItemCode' => $this->mapItemCode], $executeNow);
    }

    function pushData(array $data = [])
    {
        $dataSend = $data['listItems'] ?? [];
        $mapItemCode = $data['mapItemCode'] ?? [];
        if (!$dataSend) return false;

        $api = new Items();
        if ($api->execute(['listItems' => $dataSend])) {
            $resData = $api->getResponse()->getData();
            $result = $resData['result']['ResultOfItemsIntegration'] ?? [];
            $flowsId = $resData['result']['FlowsId'];
            foreach ($result as $transcan) {
                $productId = $mapItemCode[$transcan['ItemCode'] ?? 'NONE'];
                $p = new Product([
                    'rowid' => $productId
                ]);
                $mapping = $p->getMappingInstance();
                $mapping->transcan_id = $transcan['XtentItemId'];
                $mapping->transcan_meta_id = $transcan['ItemCode'];
                $mapping->transcan_payload = json_encode($flowsId);
                $mapping->transcan_integrate_status = null;
                $mapping->save();
            }
            return $result;
        } else {
            $errors = $api->getErrors();
            throw new TranscannSyncException(array_pop($errors), $api->getClient()->getLogs());
        }
    }
}