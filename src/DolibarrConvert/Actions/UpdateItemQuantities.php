<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Actions;

use WMS\Xtent\Apis\QueryWebServices\GetItemQuantities;
use WMS\Xtent\DolibarrConvert\Product;

class UpdateItemQuantities
{
    function execute()
    {
        $api = new GetItemQuantities();

        if ($api->execute(['filters' => "Client.Id=2000"])) {
            foreach ($api->getResponse()->getData()['result'] as $item) {
                addAction([Product::class, 'updateStockFromTranscan'], $item, true);
            }
        }
    }
}