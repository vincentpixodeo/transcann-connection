<?php

namespace WMS\Http;
class Log
{
    protected mixed $response;
    protected mixed $responseCode;
    public function __construct(protected string $method, protected string $url, protected array $body = [], protected array $headers = [])
    {}

    /**
     * @param $response
     * @return $this
     */
    function setResponse($response, $responseCode): self
    {
        $this->response = $response;
        $this->responseCode = $responseCode;
        return $this;
    }

    public function getResponse(): mixed
    {
        return $this->response;
    }
    public function getResponseCode(): mixed
    {
        return $this->responseCode;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}