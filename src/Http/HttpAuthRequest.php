<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */


namespace WMS\Xtent\Http;

use WMS\Xtent\Contracts\AbstractRequestAction;
use WMS\Xtent\Contracts\ClientInterface;
use WMS\Xtent\Contracts\HttpRequestInterface;
use WMS\Xtent\WMSAuthentication;
use WMS\Xtent\WmsXtentService;

class HttpAuthRequest extends AbstractRequestAction implements HttpRequestInterface
{
    public WMSAuthentication $authentication;

    public function __construct(ClientInterface $client = null)
    {
        $this->authentication = WmsXtentService::authentication();

        parent::__construct($this->authentication->getClient());

        $token = $this->authentication->getToken();

        $this->_client->setToken($token);
    }

    function getCurrentLog(): ?Log
    {
        return $this->_client->getCurrentLog();
    }
}