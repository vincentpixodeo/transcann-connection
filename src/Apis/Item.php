<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Apis;

use WMS\Xtent\Contracts\AbstractRequestAction;
use WMS\Xtent\Contracts\ClientInterface;
use WMS\Xtent\Contracts\RequestActionInterface;
use WMS\Xtent\WMSAuthentication;
use WMS\Xtent\WmsXtentService;

class Item extends AbstractRequestAction implements RequestActionInterface
{
    private WMSAuthentication $authentication;

    public function __construct(ClientInterface $client = null)
    {
        $this->authentication = WmsXtentService::authentication();
        parent::__construct($client ?? $this->authentication->getClient());
    }

    public function delete(string $metaId, $ids): bool
    {
        $this->uri = 'Item?' . http_build_query([
                'token' => $this->authentication->getToken(),
                'metaId' => $metaId,
                'ids' => $ids
            ]);
        $this->_method = self::METHOD_DELETE;
        return $this->execute();
    }

    public function create(array $data): ?\WMS\Xtent\Data\Item
    {
        $this->uri = 'Item?' . http_build_query(['token' => $this->authentication->getToken()]);
        $this->_method = self::METHOD_POST;
        if ($this->execute($data)) {
            return new \WMS\Xtent\Data\Item($this->getResponse()->getData());
        }
        return null;
    }

    public function put(array $data): ?\WMS\Xtent\Data\Item
    {
        $this->uri = 'Item?' . http_build_query(['token' => $this->authentication->getToken()]);
        $this->_method = self::METHOD_PUT;
        if ($this->execute($data)) {
            return new \WMS\Xtent\Data\Item($this->getResponse()->getData());
        }
        return null;
    }

}