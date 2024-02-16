<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Apis\QueryWebServices;

use WMS\Xtent\Contracts\RequestActionInterface;
use WMS\Xtent\Http\HttpAuthRequest;

class GetReceptionsStored extends HttpAuthRequest implements RequestActionInterface
{
    protected ?string $uri = 'QueryWebServices/GetReceptions/Stored';

}