<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Http;

use WMS\Contracts\HttpRequestInterface;
use WMS\WmsXtentService;

class HttpRequest implements HttpRequestInterface
{
    protected Curl $_client;

    public function __construct(string $baseUrl = null, Curl $curl = null)
    {
        is_null($baseUrl) && $baseUrl = WmsXtentService::instance()->getConfig('baseUrl');
        $this->_client = $curl ? $curl->setBaseUrl($baseUrl) : new Curl($baseUrl);
    }

    public function getClient(): Curl
    {
        return $this->_client;
    }
}