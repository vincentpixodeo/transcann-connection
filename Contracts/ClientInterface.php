<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Contracts;

use WMS\Http\Exception;
use WMS\Http\JsonException;
use WMS\Http\Log;
use WMS\Http\Response;

interface ClientInterface
{
    /**
     * @return Log[]
     */
    public function getLogs(): array;

    /**
     * @return Log|null
     */
    public function getCurrentLog(): ?Log;

    /**
     * @param string $baseUrl
     * @return $this
     */
    public function setBaseUrl(string $baseUrl): ClientInterface;

    /**
     * @param string $token
     * @return $this
     */
    public function setToken(string $token): ClientInterface;

    /**
     * @param string $uri
     * @param array $query
     * @param array $header
     * @return Response
     * @throws Exception
     */
    public function get(string $uri, array $query = [], array $header = []): Response;

    /**
     * @param string $uri
     * @param array $data
     * @param array $header
     * @return Response
     * @throws Exception
     */
    public function delete(string $uri, array $data = [], array $header = []): Response;

    /**
     * @param string $uri
     * @param array $data
     * @param array $header
     * @return Response
     * @throws Exception
     */
    public function put(string $uri, array $data = [], array $header = []): Response;

    /**
     * @param string $uri
     * @param array $data
     * @param array $header
     * @param string $customRequest value: POST|PUT|DELETE
     * @return Response
     * @throws Exception
     */
    public function post(string $uri, array $data = [], array $header = [], string $customRequest = "POST"): Response;

    /**
     * @param array $header
     * @return Response
     * @throws Exception
     * @throws JsonException
     */
    function execute(array $header = []): Response;

    public function getResponse(): Response;
}