<?php
include_once __DIR__.'/../autoloader.php';

$instance = \WMS\WmsXtentService::instance();

$path = $instance->storagePath('logs/GetItemsList');

$action = $instance->getAction(\WMS\Apis\QueryWebServices\GetItems::class);

// $data = file_get_contents($path.'/item-0.json');

// $data = json_decode($data, true);

// dd(new \WMS\Data\Item($data));

if ($action->execute([
	'filters' => 'Client.Id = 321 || Client.Id = 1 || Client.Id = 2000'
])) {
	$logger = new \WMS\Helpers\Logs\LogFile($path, true);

	$logger->write($action->getResponse()->getData()['result'], 'item');
} else {
	$logger = new \WMS\Helpers\Logs\LogFile($path);
	$logger->write(array_map(function($e){
		return $e->getMessage();
	}, $action->getErrors()), 'errors');

	dd($action->getClient()->getLogs());
}