<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Helpers\Inputs;

class DateTime
{
    const FORMAT = "YmdHi";
    function get(string $value): \DateTime
    {
        return new \DateTime($value);
    }

    function set(\DateTime $value): string
    {
         return $value->format(static::FORMAT);
    }
}