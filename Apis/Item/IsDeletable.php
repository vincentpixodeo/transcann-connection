<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Apis\Item;

use WMS\Contracts\RequestActionInterface;
use WMS\Http\HttpAuthRequest;

class IsDeletable extends HttpAuthRequest implements RequestActionInterface
{
}