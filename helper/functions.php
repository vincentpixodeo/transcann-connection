<?php

use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\DolibarrConvert\Action;
use WMS\Xtent\DolibarrConvert\ActionResult;
use WMS\Xtent\DolibarrConvert\Enums\ActionResultStatus;
use WMS\Xtent\DolibarrConvert\Enums\ActionStatus;
use WMS\Xtent\DolibarrConvert\TranscannSyncException;

/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

function getDbInstance(): \WMS\Xtent\Database\DoliDB
{

    static $db;
    if (empty($db)) {
        global $conf;
        $dbConf = $conf->db;
        $db = new \WMS\Xtent\Database\DoliDBMysqli($dbConf->type, $dbConf->host, $dbConf->user, $dbConf->pass ?? null, $dbConf->name ?? '', $dbConf->port ?? 3306);
    }
    return $db;
}

function getDbPrefix(): string
{
    global $conf;
    return $conf->db->prefix ?? '';
}

/**
 * @param array|string $name
 * @param array $data
 * @return int|string|null
 *
 */
function addAction(array|string $name, array $data = [], $executeNow = false): int|string|null
{
    if (is_array($name)) {
        list($instance, $method) = $name;
        $name = "$instance@$method";
    }

    $instance = new Action([
        'action' => $name,
        'payload' => json_encode($data)
    ]);

    $instance->save();

    if ($executeNow) {
        executeAction($instance, false);
    }

    return $instance->id();
}

function executeAction(Action $action, $allowRetry = true): void
{
    $db = getDbInstance();
    list($instance, $method) = explode('@', $action->action);
    $failStatus = $allowRetry ? ActionStatus::Init->value : ActionStatus::Processed->value;
    $action->save([
        'action' => $action->action,
        'retries' => $action->retries + 1,
        'status' => ActionStatus::Processing->value
    ]);

    $data = json_decode($action->payload, true);
    $result = new ActionResult([
        'action_id' => $action->id(),
        'payload' => $action->payload
    ]);
    try {
        if (!class_exists($instance)) {
            throw new Exception('Class not exist :' . $instance);
        }
        $result->save(['status' => ActionResultStatus::Start->value]);
        $return = (new $instance($data ?? []))->{$method}($data);
        if ($return instanceof \WMS\Xtent\Http\Log) {
            $result->save([
                'payload' => json_encode($return->getBody()),
                'status' => ActionResultStatus::Success->value,
                'response' => $return->getResponse()
            ]);
        } else {
            $return instanceof ObjectDataInterface && $return = $return->toArray();
            $result->save([
                'status' => ActionResultStatus::Success->value,
                'response' => json_encode($return ?? 'null')
            ]);
        }

        $action->save([
            'status' => ActionStatus::Processed->value,
            'last_result_id' => $result->id(),
            'last_result_status' => $result->status,
        ]);
    } catch (TranscannSyncException $exception) {
        $payload = [];
        $response = [];
        $error = $exception->getMessage();

        if ($log = $exception->getLastLog()) {
            $payload = $exception->getLogs();
            $response = $log->getResponse();
            $error = $log->getUrl();
        }

        $result->save([
            'payload' => serialize($payload),
            'response' => json_encode($response),
            'error' => $error,
            'status' => ActionResultStatus::Fail->value
        ]);
        $action->save([
            'status' => $failStatus,
            'last_result_id' => $result->id(),
            'last_result_status' => $result->status,
        ]);
    } catch (Exception $exception) {
        $result->save([
            'response' => $exception->getTraceAsString(),
            'error' => $exception->getMessage(),
            'status' => ActionResultStatus::Fail->value
        ]);
        $action->save([
            'status' => $failStatus,
            'last_result_id' => $result->id(),
            'last_result_status' => $result->status,
        ]);
    }
}


if (!function_exists('dump')) {
    function dump(): void
    {
        foreach (func_get_args() as $item) {
            print_r($item);
        }
    }
}
if (!function_exists('dd')) {
    function dd(): void
    {
        foreach (func_get_args() as $item) {
            print_r($item);
        }

        die();
    }
}