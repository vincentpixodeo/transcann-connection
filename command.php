<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */
include_once __DIR__ . '/autoloader.php';

use WMS\Xtent\DolibarrConvert\Action;


$action = new Action();
$db = getDbInstance();
$tableAction = getDbPrefix() . ltrim($action->getMainTable(), getDbPrefix());

while (true) {
    $results = $db->query("SELECT * FROM {$tableAction} where status = " . Action::STATUS_INIT . " AND retries < 10 ORDER BY created_at");

    if ($results) {
        while ($row = $db->fetch_object($results)) {
            executeAction($action->setData(array_filter((array)$row)));
            /*Clear Memory*/
            \WMS\Xtent\WmsXtentService::instance()->refresh();
        }
    } else {
        dd($db->lasterror());
    }

}