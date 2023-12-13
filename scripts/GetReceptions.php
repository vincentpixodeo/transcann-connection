<?php
include_once __DIR__.'/../autoloader.php';

$instance = \WMS\WmsXtentService::instance();


$action = $instance->getAction(\WMS\Apis\QueryWebServices\GetReceptions::class);



if ($action->execute([
	"pageNumber" => 1,
	"recordsByPage" => 10,
	"filters" => "ClientCodeId = 321"
])) {
	dd($action->getResponse()->getData());
	$logger = new \WMS\Helpers\Logs\LogFile($instance->storagePath('logs/GetReceptions'), true);

	$logger->write($action->getResponse()->getData()['result'], 'reception');
} else {
	$logger = new \WMS\Helpers\Logs\LogFile($instance->storagePath('logs/GetReceptions'));
	$logger->write(array_map(function($e){
		return $e->getMessage();
	}, $action->getErrors()), 'errors');
}