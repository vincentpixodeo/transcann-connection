<?php
include_once __DIR__.'/../autoloader.php';

$instance = \WMS\WmsXtentService::instance();


$action = $instance->getAction(\WMS\Apis\IntegrationWebServices\Items::class);



if ($action->execute()) {
	dd($action->getResponse());
	$logger = new \WMS\Helpers\Logs\LogFile($instance->storagePath('logs/GetReceptions'), true);

	$logger->write($action->getResponse()->getData()['result'], 'reception');
} else {
	dd(1, $action->getErrors());
	dd();
	$logger = new \WMS\Helpers\Logs\LogFile($instance->storagePath('logs/GetReceptions'));
	$logger->write(array_map(function($e){
		return $e->getMessage();
	}, $action->getErrors()), 'errors');
}