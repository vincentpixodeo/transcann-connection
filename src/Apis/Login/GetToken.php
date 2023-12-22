<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Apis\Login;

use WMS\Xtent\Contracts\AbstractRequestAction;
use WMS\Xtent\Contracts\RequestActionInterface;

class GetToken extends AbstractRequestAction implements RequestActionInterface
{
	protected string $_method = RequestActionInterface::METHOD_GET;
}