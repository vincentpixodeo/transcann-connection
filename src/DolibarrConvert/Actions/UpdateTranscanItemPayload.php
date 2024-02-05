<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Actions;

use WMS\Xtent\Apis\QueryWebServices\GetItems;
use WMS\Xtent\DolibarrConvert\Pivots\MappingProduct;

class UpdateTranscanItemPayload
{
    function execute()
    {
        $api = new GetItems();
        /** @var MappingProduct $map */
        foreach (MappingProduct::get(['transcan_integrate_status' => null]) as $map) {
            if ($api->execute(['filters' => 'ItemCode = "' . $map->transcan_meta_id . '"'])) {
                $data = $api->getResponse()->getData()['result'] ?? [];
                if ($data) {
                    $map->transcan_payload = json_encode($data[0]);
                    $map->transcan_integrate_status = 1;
                    $map->transcan_id = $data[0]['Id'];
                    $map->save();
                }
            }
        }

    }
}