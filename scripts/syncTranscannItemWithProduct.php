<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

include_once __DIR__ . '/../autoloader.php';

$productId = 1;
$transcannItemId = 12;
$product = new \WMS\Xtent\DolibarrConvert\Product(['rowid' => 1]);

$api = new \WMS\Xtent\Apis\Item\GetByKeys();
if ($api->load($transcannItemId)) {
    $product->updateDataFromTranscann(new \WMS\Xtent\Data\Item($api->getResponse()->getData()));
} else {
    dd($api->getErrors());
}