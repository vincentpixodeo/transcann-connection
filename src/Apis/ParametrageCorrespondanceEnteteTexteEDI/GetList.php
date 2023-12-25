<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Apis\ParametrageCorrespondanceEnteteTexteEDI;

use WMS\Xtent\Contracts\HasLoadListFunction;
use WMS\Xtent\Contracts\RequestActionInterface;
use WMS\Xtent\Http\HttpAuthRequest;

class GetList extends HttpAuthRequest implements RequestActionInterface
{
    use HasLoadListFunction;

    protected array $_data = [
        'pageNumber' => 1,
        "metaId" => "f7cfafdd-64b0-4dde-aaeb-cb9bea9f7250"
    ];

}