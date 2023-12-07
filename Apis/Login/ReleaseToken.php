<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Apis\Login;

use WMS\Contracts\AbstractRequestAction;
use WMS\Contracts\RequestActionInterface;
use WMS\Http\Curl;

class ReleaseToken extends AbstractRequestAction implements RequestActionInterface
{
    protected Curl $_client;

    protected array $_data;

    protected string $_method = RequestActionInterface::METHOD_GET;

    public function __construct(Curl $client, array $data)
    {
        $this->_client = $client;
        $this->_data = $data;
    }

    public function getClient(): Curl
    {
        return $this->_client;
    }
}