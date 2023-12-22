<?php
include_once __DIR__.'/../autoloader.php';

$instance = \WMS\Xtent\WmsXtentService::instance();


$action = $instance->getAction(\WMS\Xtent\Apis\IntegrationWebServices\Items::class);


$data = [
	'listItems' => [
		[
			"ClientCodeId"=> 2000,
            "ItemCode"=> "BP",
            "Description"=> "LA BONNE PAYE", 
            "ExternalReference"=> null,
            "BatchManagement"=> "L",
            "RotationCode"=> "A",
            "UnitCode"=> "BOI",
            "Comments"=> null,
            "Inner"=> 1,
            "Outer"=> 8,
            "ParcelsPerLayer"=> 40,
            "LayersPerPallet"=> 6,
            "SUWidth"=> 20.0,
            "SUDepth"=> 40.0,
            "SUHeight"=> 5.0,
            "Width"=> 50,
            "Depth"=> 60,
            "Height"=> 30,
            "PalletOccupationWidth"=> 100,
            "PalletOccupationDepth"=> 100,
            "PalletOccupationHeight"=> 120,
            "ParcelGrossWeight"=> 2.6,
            "ParcelNetWeight"=> 2.6,
            "SUGrossWeight"=> 0.325,
            "SUNetWeight"=> 0.325,
            "CurrencyId"=> "EUR",
            "Value"=> 12.123,
            "PackagingCode"=> "EUR",
            "InboundStatus"=> null,
            "ReturnStatus"=> "IND",
            "OutboundStatus"=> null,
            "SupplierCodeId"=> 111,
            "FamilyCode"=> "AAA",
            "EdiItemGencode"=>  [
                [
                    "ItemGencod"=> "87110003603546546416"
                ]
            ]

		]
	]
];


if ($action->execute($data)) {
	dd($action->getResponse()->getData());
	$logger = new \WMS\Xtent\Helpers\Logs\LogFile($instance->storagePath('logs/IntegrationWebServices/Receptions'), true);

	$logger->write($action->getResponse()->getData()['result'], 'reception');
} else {
	$log = $action->getClient()->getCurrentLog();
	
	$dataLog = [
		'url' => $log->getUrl(),
		'body' => $log->getBody(),
		'responseCode' => $log->getResponseCode(),
		'response' => $log->getResponse()
	];
	
	$logger = new \WMS\Xtent\Helpers\Logs\LogFile($instance->storagePath('logs/IntegrationWebServices/Items'));
	$logger->write($dataLog, 'errors'.date_create()->format('Y-m-d H-i-s'));
}