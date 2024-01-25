<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

use WMS\Xtent\Data\Enums\Meta;
use WMS\Xtent\DolibarrConvert\Unit;

include_once __DIR__ . '/../autoloader.php';

$unit = new Unit();


foreach ($unit->list(['active' => 1]) as $item) {
    /** @var Unit $item */
    $item->getMappingInstance(['transcan_id' => $item->code, 'transcan_meta_id' => Meta::Unit->value])->save();
}