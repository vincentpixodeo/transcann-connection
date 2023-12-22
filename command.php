<?php
include_once __DIR__.'/autoloader.php';


$instance = \WMS\Xtent\WmsXtentService::instance();

dd($instance);
$content = file_get_contents(__DIR__.'/../response1.json');
//
//$data = json_decode($content, true)['result'];
//$path = $instance->storagePath('/logs/receptions/');
//
//$logger = new \WMS\Helpers\Logs\LogFile($path, true);
//
//$logger->write($data, 'reception');
//dd(1);
foreach (json_decode($content, true)['result'] as $reception) {

    dd(new \WMS\Xtent\Data\Reception($reception));
}


$action = $instance->getAction(\WMS\Xtent\Apis\Item::class);

if ($action->execute()) {
    dd($action->getClient()->getLogs(), $action->getResponse()->getCode());
} else {
    dd($action->getErrors());
}
