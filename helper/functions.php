<?php

use WMS\Xtent\DolibarrConvert\Action;
use WMS\Xtent\DolibarrConvert\ActionResult;
use WMS\Xtent\DolibarrConvert\TranscannSyncException;

/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

function getDbInstance(): DoliDB
{
    static $db;
    if (empty($db)) {
        global $conf;
        $dbConf = $conf->db;
        $db = new DoliDBMysqli($dbConf->type, $dbConf->host, $dbConf->user, $dbConf->pass ?? null, $dbConf->name ?? '', $dbConf->port ?? 3306);
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
 * @throws Exception
 */
function addAction(array|string $name, array $data): int|string|null
{
    if (is_array($name)) {
        list($instance, $method) = $name;
        $name = "$instance@$method";
    }

    $instance = new Action([
        'action' => addslashes($name),
        'payload' => json_encode($data)
    ]);

    $instance->save();

    return $instance->id();
}

function executeAction(Action $action): void
{
    $db = getDbInstance();
    list($instance, $method) = explode('@', $action->action);
    $action->save([
        'action' => addslashes($action->action),
        'retries' => $action->retries + 1,
        'status' => Action::STATUS_PROCESSING
    ]);

    $data = json_decode($action->payload, true);
    $result = new ActionResult(['action_id' => $action->id()]);
    try {
        if (!class_exists($instance)) {
            throw new Exception('Class not exist :' . $instance);
        }
        $result->save(['status' => ActionResult::STATUS_START]);
        (new $instance($data))->{$method}();
        $result->save(['status' => ActionResult::STATUS_SUCCESS]);
        $action->save([
            'status' => Action::STATUS_PROCESSED
        ]);
    } catch (TranscannSyncException $exception) {
        $payload = [];
        $response = [];
        $error = $exception->getMessage();

        if ($log = $exception->getLastLog()) {
            $payload = $log->getBody();
            $response = $log->getResponse();
            $error = $log->getUrl();
        }
        $result->save([
            'payload' => $db->escape(json_encode($payload)),
            'response' => $db->escape(json_encode($response)),
            'error' => $db->escape($error),
            'status' => ActionResult::STATUS_FAIL
        ]);
        $action->save([
            'status' => Action::STATUS_INIT
        ]);
    } catch (Exception $exception) {
        $action->save([
            'status' => Action::STATUS_INIT
        ]);
        $result->save([
            'response' => $db->escape($exception->getTraceAsString()),
            'error' => $db->escape($exception->getMessage()),
            'status' => ActionResult::STATUS_FAIL
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