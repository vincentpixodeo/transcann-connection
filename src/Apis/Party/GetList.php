<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Apis\Party;

use WMS\Xtent\Contracts\HasLoadListFunction;
use WMS\Xtent\Contracts\RequestActionInterface;
use WMS\Xtent\Http\HttpAuthRequest;

class GetList extends HttpAuthRequest implements RequestActionInterface
{
    use HasLoadListFunction;

    protected array $_data = [
        'pageNumber' => 1,
        "metaId" => "dc10fbc6-e9eb-4bd0-8871-a0cf2ed27ac0"
    ];

}