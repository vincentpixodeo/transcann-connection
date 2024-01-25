<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

use WMS\Xtent\DolibarrConvert\Category;

include_once __DIR__ . '/../autoloader.php';

$category = new Category();


foreach ($category->list() as $item) {
    /** @var Category $item */
    $transcannId = match ($item->label) {
        "A REMPLIR" => 0,
        "FRAIS" => 20,
        "SURGELE" => 30,
        "HYGIENES & DROGUERIE" => 50,
        "EPICERIE" => 10,
        "MAGASIN GLACIER" => 81,
        "LIQUIDES" => 40,
        "PRIMEURS" => 70,
        "MATERIELS" => 60,
        "CHRISTOPHE ARTISAN G" => 80,
        "PANIER" => 90,
        "GLACES ARTISANALES" => 0,
    };
    if ($transcannId) {
        $item->getMappingInstance(['transcan_id' => $transcannId, 'transcan_meta_id' => \WMS\Xtent\Data\Enums\Meta::Family->value]);
    }
}