<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Contracts;

use Exception;
use WMS\Xtent\Http\Response;
use WMS\Xtent\WmsXtentService;

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

    public function validate(array $data): bool
    {
        if (!in_array($this->_method, [
            RequestActionInterface::METHOD_GET,
            RequestActionInterface::METHOD_POST,
            RequestActionInterface::METHOD_PUT,
            RequestActionInterface::METHOD_DELETE,
        ])) {
            $this->addError("{$this->_method} invalid");
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
            $uri = preg_replace('/WMS\\\Xtent\\\/', '', static::class);
            $uri = preg_replace('/Apis\\\/', '', $uri);
            $uri = preg_replace('/\\\/', '/', $uri);
            $this->uri = $uri;
        }

        return $this->uri;
    }

    /**
     * @throws Exception
     */
    protected function requestApi(array $data = [], array $headers = []): bool
    {
        $dataSend = array_merge($this->_data, $data);
        if ($this->validate($dataSend)) {
            $this->_response = $this->getClient()->{strtolower($this->_method)}(
                $this->getUri(),
                $dataSend,
                array_merge($this->_headers, $headers)
            );
            if ($this->getResponse() && $this->getResponse()->getCode() > 300 && $data = $this->getResponse()->getData()) {
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

    protected function addError(string $message): static
    {
        $this->_errors[] = new Exception($message);
        return $this;
    }

    /**
     * @param string|null $uri
     */
    public function setUri(?string $uri): void
    {
        $this->uri = $uri;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->_method = $method;
    }

}