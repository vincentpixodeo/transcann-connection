<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Apis\QueryWebServices;

use WMS\Xtent\Contracts\RequestActionInterface;
use WMS\Xtent\Http\HttpAuthRequest;

class CheckFlowIntegrationStatus extends HttpAuthRequest implements RequestActionInterface
{
    function execute(...$arguments): bool
    {
        $flowId = $arguments[0] ?? null;
        if (empty($flowId)) {
            $this->addError('flowID is empty');
        }
        return parent::execute(["flowId" => $arguments[0] ?? null]);
    }
}