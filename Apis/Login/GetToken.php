<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Apis\Login;

use WMS\Contracts\AbstractRequestAction;
use WMS\Contracts\ClientInterface;
use WMS\Contracts\RequestActionInterface;
use WMS\Http\Curl;
use WMS\Http\Response;
use WMS\WmsXtentService;

class GetToken extends AbstractRequestAction implements RequestActionInterface
{
}