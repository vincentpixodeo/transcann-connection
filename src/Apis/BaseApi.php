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

abstract class BaseApi extends AbstractRequestAction implements RequestActionInterface
{
    protected WMSAuthentication $authentication;

    public function __construct(ClientInterface $client = null)
    {
        $this->authentication = WmsXtentService::authentication();
        parent::__construct($client ?? $this->authentication->getClient());
    }

    private function getBaseUri(): string
    {
        $uri = preg_replace('/WMS\\\Xtent\\\/', '', static::class);
        $uri = preg_replace('/Apis\\\/', '', $uri);
        return preg_replace('/\\\/', '/', $uri);

    }

    private function getToken(): ?string
    {
        try {
            return $this->authentication->getToken(false, true);
        } catch (\Exception $exception) {
            $this->_errors[] = $exception;
            return null;
        }
    }

    public function delete(string $metaId, $ids): bool
    {
        $this->uri = $this->getBaseUri() . '?' . http_build_query([
                'token' => $this->getToken(),
                'metaId' => $metaId,
                'ids' => $ids
            ]);
        $this->_method = self::METHOD_DELETE;
        return $this->execute();
    }

    public function create(array $data): bool
    {

        $this->uri = $this->getBaseUri() . '?' . http_build_query(['token' => $this->getToken()]);
        $this->_method = self::METHOD_POST;
        return $this->execute($data);
    }

    public function put(int $id, array $data): bool
    {
        $this->uri = $this->getBaseUri() . '?' . http_build_query(['token' => $this->getToken()]);
        $this->_method = self::METHOD_PUT;
        $data['Id'] = $id;
        return $this->execute($data);
    }
}