<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Actions;

use WMS\Xtent\Database\Builder\QueryBuilder;
use WMS\Xtent\DolibarrConvert\Pivots\MappingReception;

class CheckEntityIntegrationStatus
{
    static function reception(): void
    {
        $list = MappingReception::get([
            'transcan_integrate_status' => null,
            'transcan_id is not null'
        ], function (QueryBuilder $queryBuilder) {
            $queryBuilder->select(['id', 'transcan_id']);
        });
        $integrateIds = [];
        foreach ($list as $mapping) {
            $integrateIds[] = $mapping->transcan_id;
        }

        $api = new \WMS\Xtent\Apis\QueryWebServices\CheckEntityIntegrationStatus();

        if ($api->execute([
            "typeEntities" => "Reception",
            "listOfEntitiesIds" => $integrateIds
        ])) {
            foreach ($api->getResponse()->getData()['result'] as $item) {
                if ("OK" == ($item['EntitieStatus'])) {
                    MappingReception::update(['transcan_integrate_status' => 1], ['transcan_id' => $item['EntitieId']]);
                } else {
                    MappingReception::update([
                        'transcan_integrate_status' => 2,
                        'transcan_payload' => json_encode($item)
                    ], ['transcan_id' => $item['EntitieId']]);
                }
            }
        }
    }
}