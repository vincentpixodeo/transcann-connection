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

    foreach (Action::get(['status' => Action::STATUS_INIT, 'retries <=' => 13]) as $row) {

        executeAction($row);
        /*Clear Memory*/
        \WMS\Xtent\WmsXtentService::instance()->refresh();
    }

    sleep(15);
}