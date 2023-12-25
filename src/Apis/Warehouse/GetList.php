<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Apis\Warehouse;

use WMS\Xtent\Contracts\HasLoadListFunction;
use WMS\Xtent\Contracts\RequestActionInterface;
use WMS\Xtent\Http\HttpAuthRequest;

class GetList extends HttpAuthRequest implements RequestActionInterface
{
    use HasLoadListFunction;

    protected array $_data = [
        'pageNumber' => 1,
        "metaId" => "0738d10b-f47c-4801-9c97-f480d373f04c"
    ];
}