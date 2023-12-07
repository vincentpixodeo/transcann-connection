<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Apis\QueryWebServices;

use WMS\Contracts\RequestActionInterface;
use WMS\Http\HttpAuthRequest;

class GetPreparationsSSCC extends HttpAuthRequest implements RequestActionInterface
{
    protected ?string $uri = 'QueryWebServices/GetPreparations/SSCC';

}