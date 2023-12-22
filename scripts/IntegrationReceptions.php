<?php
include_once __DIR__.'/../autoloader.php';

$instance = \WMS\Xtent\WmsXtentService::instance();


$action = $instance->getAction(\WMS\Xtent\Apis\IntegrationWebServices\Receptions::class);


$data = [
	'listReceptions' => [
		[
			"Order"=> "TEST11111111",
            "SupplierReference" => "CRG SOSKIN",
            "ClientCodeId" => "321",
            "MovementCodeId" => "ENT",
            "AppointmentDate"=> null,
            "ArrivalDate"=> null,
            "DateOfPlannedReceiving"=> "202312141415",
            "Comments" => "INTEGRE PAR API",
            "EdiReceptionDetailsList" => [
                [
                    "BatchNumber"=> null,
                    "Comments"=> null,
                    "CRD"=> null,
                    "CurrencyId"=> null,
                    "CustomizedDate1"=> null,
                    "CustomizedDate2"=> null,
                    "CustomizedDate3"=> null,
                    "CustomizedDate4"=> null,
                    "CustomizedDate5"=> null,
                    "CustomizedField1"=> null,
                    "CustomizedField10"=> null,
                    "CustomizedField2"=> null,
                    "CustomizedField3"=> null,
                    "CustomizedField4"=> null,
                    "CustomizedField5"=> null,
                    "CustomizedField6"=> null,
                    "CustomizedField7"=> null,
                    "CustomizedField8"=> null,
                    "CustomizedField9"=> null,
                    "DAEDSANumber"=> null,
                    "DAEDSAType"=> null,
                    "ExpectedFullPallet"=> null,
                    "ExpectedParcel"=> "1",
                    "ExpectedSaleUnit"=> null,
                    "ExpiryDate"=> null,
                    "ExternalItemId"=> null,
                    "ExternalLineNumber"=> null,
                    "GrossWeight"=> null,
                    "Id" => 0,
                    "Inner"=> null,
                    "InternalItemId"=> "CRG01SOS30120",
                    "ItemCode"=> "CRG01SOS30120",
                    "LayersPerPallet"=> null,
                    "LineNumber" => "010",
                    "MultiReferencePalletId"=> null,
                    "NetWeight"=> null,
                    "Outer"=> null,
                    "PackagingCode"=> null,
                    "Pallet" => "PAL0123456789",
                    "PalletOccupationDepth"=> null,
                    "PalletOccupationHeight"=> null,
                    "PalletOccupationWidth"=> null,
                    "ParcelsPerLayer"=> null,
                    "StatusCodeId"=> null,
                    "Value"=> null
                ]
            ]
		]
	]
];


if ($action->execute($data)) {
	dd($action->getResponse());
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
	
	$logger = new \WMS\Xtent\Helpers\Logs\LogFile($instance->storagePath('logs/IntegrationWebServices/Receptions'));
	$logger->write($dataLog, 'errors');
}