<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */
include_once __DIR__ . '/autoloader.php';

use WMS\Xtent\DolibarrConvert\Action;
use WMS\Xtent\DolibarrConvert\Enums\ActionStatus;

$maxTries = 3;

while (true) {

    $processed = [];
    /** @var Action $action */
    foreach (Action::get(['status' => ActionStatus::Init->value, 'retries <' => $maxTries]) as $action) {
        $data = json_decode($action->payload, true);
        $key = $action->action . "::" . ($data['rowid'] ?? null);
        if ($processed[$key] ?? null) {
            $action->delete();
        } else {
            executeAction($action);
            if ($action->status == ActionStatus::Processed->value || ($action->status == ActionStatus::Init->value && $action->retries < $maxTries)) {
                $processed[$key] = true;
            }
        }

        /*Clear Memory*/
        \WMS\Xtent\WmsXtentService::instance()->refresh();
    }
    dump(memory_get_peak_usage());
    sleep(15);
}