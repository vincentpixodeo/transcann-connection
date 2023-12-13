<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Contracts;

use WMS\Http\Curl;

interface HttpRequestInterface
{
    public function getClient(): ClientInterface;
}