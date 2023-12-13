<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Contracts;

use Exception;
use WMS\Http\JsonException;
use WMS\Http\Response;
use WMS\WmsXtentService;

abstract class AbstractRequestAction implements RequestActionInterface
{
    protected ?ClientInterface $_client = null;

    protected ?string $uri = null;

    protected string $_method = 'POST';

    protected array $_data = [];

    protected array $_headers = [];

    protected array $_errors = [];

    protected ?Response $_response = null;

    public function __construct(ClientInterface $client = null)
    {

        is_null($client) && $client = WmsXtentService::instance()->getClient();
        $this->_client = $client;
    }

    public function getClient(): ClientInterface
    {
        return $this->_client;
    }

    public function validate(): bool
    {
        if (!in_array($this->_method, [
            RequestActionInterface::METHOD_GET,
            RequestActionInterface::METHOD_POST,
            RequestActionInterface::METHOD_PUT,
            RequestActionInterface::METHOD_DELETE,
        ])) {
            $this->_errors[] = new Exception("{$this->_method} invalid");
        }

        return empty($this->_errors);
    }


    public function getResponse(): ?Response
    {
        if (is_null($this->_response) && $log = $this->getClient()->getCurrentLog()) {
            $this->_response = $this->getClient()->getResponse();
        }
        return $this->_response;
    }

    function getUri(): string
    {
        if (empty($this->uri)) {
            $uri = preg_replace('/WMS\\\/', '',static::class);
            $uri = preg_replace('/Apis\\\/', '',$uri);
            $uri = preg_replace('/\\\/', '/',$uri);
            $this->uri = $uri;
        }

        return $this->uri;
    }

    /**
     * @throws Exception
     */
    protected function requestApi(array $data = [], array $headers = []): bool
    {
        if ($this->validate()) {
            $this->_response = $this->getClient()->{strtolower($this->_method)}(
                $this->getUri(),
                array_merge($this->_data, $data),
                array_merge($this->_headers, $headers)
            );
            if ($this->getResponse() || $this->getResponse()->getCode() > 300 && $data = $this->getResponse()->getData()) {
                if (is_array($data) && isset($data['Message'])) {
                    throw new Exception($data['Message'], $this->getResponse()->getCode());
                }
            }
            return true;
        }
        return false;
    }

    public function execute(...$arguments): bool
    {
        try {
            return $this->requestApi(...$arguments);
        } catch (Exception $exception) {
            $this->_errors[] = $exception;
            return false;
        }
    }

    public function getErrors(): array
    {
        return $this->_errors;
    }

}