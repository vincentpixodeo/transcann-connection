<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

include_once __DIR__ . '/../autoloader.php';

$path = \WMS\Xtent\WmsXtentService::instance()->storagePath('databases/products');

$productId = 1;

$productId = $argv[1] ?? null;

$pushToTranscann = empty($argv[2]);

$pathFile = $path . "/item-{$productId}.json";
if (empty($productId) || !file_exists($pathFile)) {
    throw new Exception("Product #{$productId} not found!");
}

$data = json_decode(file_get_contents($pathFile), true);

$product = new \WMS\Xtent\DolibarrConvert\Product($data);
$product->setData(['client_id' => 2000]);

if ($pushToTranscann) {

    $product->pushDataToTranscann();

} else {
    $mapping = $product->getMappingInstanceByObjectId($productId);

    $transcannItemId = $mapping['transcann_id'] ?? null;
    if ($transcannItemId) {
        $api = new \WMS\Xtent\Apis\Item\GetByKeys();
        if ($api->load($transcannItemId)) {
            $product->updateDataFromTranscann(new \WMS\Xtent\Data\Item($api->getResponse()->getData()));
        } else {
            dd($api->getErrors());
        }
    } else {
        throw new Exception('the mapping not exist');
    }

}
