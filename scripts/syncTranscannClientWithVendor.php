<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

include_once __DIR__ . '/../autoloader.php';

$path = \WMS\Xtent\WmsXtentService::instance()->storagePath('databases/vendors');

$vendorId = $argv[1] ?? null;

$pushToTranscann = empty($argv[2]);

$pathFile = $path . "/item-{$vendorId}.json";
if (empty($vendorId) || !file_exists($pathFile)) {
    throw new Exception("Vendor #{$vendorId} not found!");
}

$data = json_decode(file_get_contents($pathFile), true);

$vendor = new \WMS\Xtent\DolibarrConvert\Vendor($data);

if ($pushToTranscann) {

    $vendor->pushDataToTranscann();

} else {
    $mapping = $vendor->getMappingInstance()->fetch();

    $transcannItemId = $mapping['transcann_id'] ?? null;
    if ($transcannItemId) {
        $api = new \WMS\Xtent\Apis\Item\GetByKeys();
        if ($api->load($transcannItemId)) {
            $vendor->updateDataFromTranscann(new \WMS\Xtent\Data\Client($api->getResponse()->getData()));
        } else {
            dd($api->getErrors());
        }
    } else {
        throw new Exception('the mapping not exist');
    }

}
