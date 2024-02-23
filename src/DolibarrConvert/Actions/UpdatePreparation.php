<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Actions;

use WMS\Xtent\DolibarrConvert\Shipping;

class UpdatePreparation
{
    function execute()
    {
        $api = new \WMS\Xtent\Apis\QueryWebServices\GetPreparationsPrepared();

        if ($api->execute(['filters' => 'Client.id = 2000'])) {
            foreach ($api->getResponse()->getData()['result'] as $preparationData) {
                addAction([Shipping::class, 'createFromTranscan'], $preparationData, true);
            }
        }
    }
}