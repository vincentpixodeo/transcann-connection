<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Apis;

use WMS\Xtent\Contracts\RequestActionInterface;
use WMS\Xtent\Http\HttpAuthRequest;

class ParametrageCorrespondanceEnteteTexteEDI extends HttpAuthRequest implements RequestActionInterface
{
    function getList(int $pageNumber = 1, int $recordByPage = 20): bool
    {
        $this->uri = 'ParametrageCorrespondanceEnteteTexteEDI/GetList';
        return $this->execute([
            'pageNumber' => $pageNumber,
            'recordByPage' => $recordByPage,
            "metaId" => "f7cfafdd-64b0-4dde-aaeb-cb9bea9f7250"
        ]);
    }

    function getByKeys(int $key): bool
    {
        $this->uri = 'ParametrageCorrespondanceEnteteTexteEDI/GetByKeys';
        return $this->execute([
            'keys' => [$key]
        ]);
    }
}