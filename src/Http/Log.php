<?php

namespace WMS\Xtent\Http;
class Log
{
    protected mixed $response;
    protected mixed $responseCode;

    public function __construct(protected string $method, protected string $url, protected array $body = [], protected array $headers = [])
    {
    }

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

    public function __serialize(): array
    {
        return [
            'url' => $this->url,
            'headers' => $this->headers,
            'body' => $this->body,
            'method' => $this->method,
            'response' => $this->response,
            'responseCode' => $this->responseCode,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->url = $data['url'] ?? '';
        $this->headers = $data['headers'] ?? [];
        $this->body = $data['body'] ?? [];
        $this->method = $data['method'] ?? '';
        $this->response = $data['response'] ?? null;
        $this->responseCode = $data['responseCode'] ?? null;
    }
}