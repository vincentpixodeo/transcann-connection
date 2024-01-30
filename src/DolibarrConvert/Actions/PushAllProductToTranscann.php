<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Actions;

use Spatie\Async\Pool;
use WMS\Xtent\Apis\IntegrationWebServices\Items;
use WMS\Xtent\DolibarrConvert\Product;

class PushAllProductToTranscann
{
    const NUM_LINES = 10;

    public array $dataSend = [];
    public array $mapItemCode = [];

    function execute()
    {

        /** @var Product $product */
        foreach (Product::get() as $product) {
            $this->mapItemCode[$product->ref] = $product->rowid;
            $this->dataSend[] = $product->convertToTranscan()->toArray();
            if (count($this->dataSend) == self::NUM_LINES) {
                $this->pushData();

            }
        }
    }

    function pushData()
    {
        if (!$this->dataSend) return;

        $api = new Items();
        if ($api->execute(['listItems' => $this->dataSend])) {
            $resData = $api->getResponse()->getData();
            $result = $resData['result']['ResultOfItemsIntegration'] ?? [];
            $flowsId = $resData['result']['FlowsId'];
            foreach ($result as $transcan) {
                $productId = $this->mapItemCode[$transcan['ItemCode'] ?? 'NONE'];
                $p = new Product([
                    'rowid' => $productId
                ]);
                $mapping = $p->getMappingInstance();
                $mapping->transcan_id = $transcan['XtentItemId'];
                $mapping->transcan_meta_id = $transcan['ItemCode'];
                $mapping->transcan_payload = json_encode($flowsId);
                $mapping->save();
            }
        } else {
            dump($api->getClient()->getCurrentLog());
        }
        $this->dataSend = [];
    }
}