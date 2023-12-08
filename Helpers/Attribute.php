<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Helpers;

use WMS\Data\Collection;

final class Attribute
{
    const SYSTEM_INSTANCES = ['int', 'string', 'float', 'boolean', 'bool', 'array'];
    public function __construct(
        protected string $property,
        protected string $instance,
        protected bool $isArray,
        protected string $description = '')
    {
    }


    /**
     * @param $data
     * @return mixed
     */
    function convertData($data): mixed
    {
        if (in_array($this->instance, self::SYSTEM_INSTANCES)) {
            return $this->_convertSystemData($data);
        }

        if ($this->isArray) {
            $data =  array_map(function($item){
                return new $this->instance($item);
            }, $data);
            return new Collection($data);
        }
        return new $this->instance($data);
    }

    private function _convertSystemData($data)
    {
        return $data;
        switch ($this->instance) {
            case 'int': return (int) $data;

            case 'bool':
            case 'boolean': return (bool) $data;

            case 'float': return (float) $data;

            default: return $data;
        }
    }
}