<?php
include_once __DIR__ . '/../autoloader.php';

$result = getDbInstance()->query('SELECT MAX(CAST(ref AS SIGNED)) AS max_id FROM llx_product WHERE CAST(ref AS SIGNED) BETWEEN 10000 AND 20000');

$instance = \WMS\Xtent\WmsXtentService::instance();
$path = \WMS\Xtent\WmsXtentService::instance()->storagePath('databases/products');

$action = $instance->getAction(\WMS\Xtent\Apis\IntegrationWebServices\Items::class);

$getItemsApi = new \WMS\Xtent\Apis\QueryWebServices\GetItems();


$productId = $argv[1] ?? null;

$pathFile = $path . "/item-{$productId}.json";
if (empty($productId) || !file_exists($pathFile)) {
    throw new Exception("Product #{$productId} not found!");
}

$data = json_decode(file_get_contents($pathFile), true);

$product = new \WMS\Xtent\DolibarrConvert\Product($data);

if ($getItemsApi->execute(['filters' => json_encode('ItemCode = "' . $product->ref . '"')])) {
    dd($getItemsApi->getResponse()->get());
} else {
    dd($getItemsApi->getClient()->getCurrentLog());
}

$data = [
    'listItems' => [
        [
            "ClientCodeId" => 2000,
            "ItemCode" => $product->ref,
            "Description" => $product->label,
            "ExternalReference" => null,
            "BatchManagement" => "L",
            "RotationCode" => "B",
            "UnitCode" => $product->contenance,
            "Comments" => null,
            "Inner" => 1,
            "Outer" => $product->unitecarton,
            "ParcelsPerLayer" => 40,
            "LayersPerPallet" => 6,
            "SUWidth" => 20.0,
            "SUDepth" => 40.0,
            "SUHeight" => 5.0,
            "Width" => 50,
            "Depth" => 60,
            "Height" => 30,
            "PalletOccupationWidth" => 100,
            "PalletOccupationDepth" => 100,
            "PalletOccupationHeight" => 120,
            "ParcelGrossWeight" => 2.6,
            "ParcelNetWeight" => 2.6,
            "SUGrossWeight" => $product->poidsbrut,
            "SUNetWeight" => $product->poidsnet,
            "CurrencyId" => "EUR",
            "Value" => 12.123,
            "PackagingCode" => "EUR",
            "InboundStatus" => null,
            "ReturnStatus" => "IND",
            "OutboundStatus" => null,
            "SupplierCodeId" => 111,
            "FamilyCode" => "AAA",
            "EdiItemGencode" => [
                [
                    "ItemGencod" => $product->barcode
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
    $logger->write($dataLog, 'errors' . date_create()->format('Y-m-d H-i-s'));
}