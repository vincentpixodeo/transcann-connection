<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Contracts;

interface HttpRequestInterface
{
    public function getClient(): ClientInterface;
}