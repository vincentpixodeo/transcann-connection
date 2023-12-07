<?php
include_once __DIR__.'/autoloader.php';

$instance = \WMS\WmsXtentService::instance();

//dump($instance->getAuthentication()->getToken(true));
//
//dump($instance->getAuthentication()->getLogs());

$action = $instance->getAction(\WMS\Apis\Item::class);

dump($action->execute());

dd($action->getClient()->getLogs(), $action->getResponse()->getCode());