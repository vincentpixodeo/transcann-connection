<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Apis\Item;

use WMS\Xtent\Contracts\HasLoadListFunction;
use WMS\Xtent\Contracts\RequestActionInterface;
use WMS\Xtent\Http\HttpAuthRequest;

class GetList extends HttpAuthRequest implements RequestActionInterface
{
    use HasLoadListFunction;

    protected array $_data = [
        'pageNumber' => 1,
        "metaId" => "d018553b-afbe-4668-ab97-debc2a8adc3a"
    ];
}