<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Contracts;

use Exception;
use WMS\Http\Curl;
use WMS\Http\Log;
use WMS\Http\Response;

interface RequestActionInterface
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';

    /**
     * Get Uri
     * @return string
     */
    public function getUri(): string;

    /**
     * Validate action
     * @return bool
     */
    public function validate(): bool;

    /**
     * get Client Request
     * @return ClientInterface
     */
    public function getClient(): ClientInterface;

    /**
     * do Action
     * @param ...$arguments
     * @return bool
     */
    public function execute(...$arguments): bool;

    /**
     * @return Response|null
     */
    public function getResponse(): ?Response;


    /**
     * @return Exception[]
     */
    public function getErrors(): array;
}