<?php
include_once __DIR__.'/../autoloader.php';

$instance = \WMS\WmsXtentService::instance();


$action = $instance->getAction(\WMS\Apis\Party\GetList::class);



if ($action->execute()) {
	$logger = new \WMS\Helpers\Logs\LogFile($instance->storagePath('logs/GetClientList'), true);

	$logger->write($action->getResponse()->getData()['result'], 'client');
} else {
	$logger = new \WMS\Helpers\Logs\LogFile($instance->storagePath('logs/GetClientList'));
	$logger->write(array_map(function($e){
		return $e->getMessage();
	}, $action->getErrors()), 'errors');
}