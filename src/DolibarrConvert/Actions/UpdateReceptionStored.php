<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Actions;

use WMS\Xtent\DolibarrConvert\Reception;

class UpdateReceptionStored
{
    function execute()
    {
        $api = new \WMS\Xtent\Apis\QueryWebServices\GetReceptionsStored();

        if ($api->execute(['filters' => 'Client.id = 2000'])) {
            foreach ($api->getResponse()->getData()['result'] as $receptionData) {
                addAction([Reception::class, 'createFromTranscan'], $receptionData, true);
            }
        }
    }
}