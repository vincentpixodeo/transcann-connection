<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Apis\Office;

use WMS\Xtent\Contracts\HasLoadListFunction;
use WMS\Xtent\Contracts\RequestActionInterface;
use WMS\Xtent\Http\HttpAuthRequest;

class GetList extends HttpAuthRequest implements RequestActionInterface
{
    use HasLoadListFunction;
    
    protected array $_data = [
        'pageNumber' => 1,
        "metaId" => "b61097cf-2c4c-4f51-b5db-5e1dab1d19fc"
    ];
}