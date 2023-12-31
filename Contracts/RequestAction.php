<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Contracts;

use Exception;
use WMS\Http\Response;

abstract class RequestAction implements RequestActionInterface
{
    protected ?string $uri = null;

    protected string $_method = 'POST';

    protected array $_data = [];

    protected array $_headers = [];

    protected array $_errors = [];

    protected ?Response $_response = null;
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
            return true;
        }
        return false;
    }

    public function execute(...$arguments): bool
    {
        try {
            $this->requestApi(...$arguments);
            return true;
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