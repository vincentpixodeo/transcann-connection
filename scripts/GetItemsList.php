<?php
include_once __DIR__.'/../autoloader.php';

$instance = \WMS\Xtent\WmsXtentService::instance();
$clientId = 2000;
$path = $instance->storagePath('logs/GetItemsList/'. $clientId);

$action = $instance->getAction(\WMS\Xtent\Apis\QueryWebServices\GetItems::class);

// $data = file_get_contents($path.'/item-0.json');

// $data = json_decode($data, true);

// dd(new \WMS\Data\Item($data));

if ($action->execute([
	'filters' => 'Client.Id = '.$clientId
])) {
	$logger = new \WMS\Xtent\Helpers\Logs\LogFile($path, true);

	$logger->write($action->getResponse()->getData()['result'], 'item');
} else {
	$logger = new \WMS\Xtent\Helpers\Logs\LogFile($path);
	$logger->write(array_map(function($e){
		return $e->getMessage();
	}, $action->getErrors()), 'errors');

	dd($action->getClient()->getLogs());
}