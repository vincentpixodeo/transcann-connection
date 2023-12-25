<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Contracts;

use Exception;
use WMS\Xtent\Http\Response;

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
     * @param array $data
     * @return bool
     */
    public function validate(array $data): bool;

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