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

class ReleaseToken extends AbstractRequestAction implements RequestActionInterface
{
    protected string $_method = RequestActionInterface::METHOD_GET;
}