<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Contracts;

trait HasLoadListFunction
{
    function load(int $pageNumber = 1, int $recordByPage = 20): bool
    {
        return $this->execute([
            'pageNumber' => $pageNumber,
            'recordByPage' => $recordByPage,
        ]);
    }

    abstract function execute(...$arguments): bool;
}