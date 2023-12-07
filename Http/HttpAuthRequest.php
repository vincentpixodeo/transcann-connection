<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */


namespace WMS\Http;

use WMS\Contracts\HttpRequestInterface;
use WMS\Contracts\RequestAction;
use WMS\WMSAuthentication;
use WMS\WmsXtentService;

class HttpAuthRequest extends RequestAction implements HttpRequestInterface
{
    public WMSAuthentication $authentication;

    protected $_client;
    protected array $_data = [];
    protected array $_headers = [];

    public function __construct()
    {
        $this->authentication = WmsXtentService::instance()->getAuthentication();
    }

    /**
     * @throws \Exception
     */
    public function getClient(): Curl
    {
        if ($this->_client)
            return $this->_client;

        $this->_client = $this->authentication->getClient();
        $token = $this->authentication->getToken();
        if (empty($token)) {
            throw new \Exception('The token is empty');
        }
        $this->_client->setToken($this->authentication->getToken());
        return $this->_client;
    }

}