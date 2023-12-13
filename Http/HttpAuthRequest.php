<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */


namespace WMS\Http;

use WMS\Contracts\ClientInterface;
use WMS\Contracts\HttpRequestInterface;
use WMS\Contracts\AbstractRequestAction;
use WMS\WMSAuthentication;
use WMS\WmsXtentService;

class HttpAuthRequest extends AbstractRequestAction implements HttpRequestInterface
{
    public WMSAuthentication $authentication;

    public function __construct(ClientInterface $client = null)
    {
        $this->authentication = WmsXtentService::instance()->getAuthentication();
        parent::__construct($this->authentication->getClient());
    }

    /**
     * @throws \Exception
     */
    public function getClient(): ClientInterface
    {
        $token = $this->authentication->getToken();

        if (empty($token)) {
            throw new \Exception('The token is empty');
        }
        $this->_client->setToken($this->authentication->getToken());
        return $this->_client;
    }

}