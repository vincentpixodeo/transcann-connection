<?php
include_once __DIR__.'/../autoloader.php';

$instance = \WMS\Xtent\WmsXtentService::instance();


$action = $instance->getAction(\WMS\Xtent\Apis\Party\GetList::class);



if ($action->execute()) {
	$logger = new \WMS\Xtent\Helpers\Logs\LogFile($instance->storagePath('logs/GetClientList'), true);

	$logger->write($action->getResponse()->getData()['result'], 'client');
} else {
	$logger = new \WMS\Xtent\Helpers\Logs\LogFile($instance->storagePath('logs/GetClientList'));
	$logger->write(array_map(function($e){
		return $e->getMessage();
	}, $action->getErrors()), 'errors');
}