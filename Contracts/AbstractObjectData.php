<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Contracts;

use WMS\Contracts\ObjectDataInterface;

class AbstractObjectData implements ObjectDataInterface
{

    protected array $_data;

    public function __construct(array $data = [])
    {
        $this->_data = $data;
    }

    public function setData(array $data): static
    {
        $this->_data = $data;
        return $this;
    }

    public function addData(array $data): static
    {
        $this->_data = $data;
        return $this;
    }

    public function getData(string $key = null, $default = null): mixed
    {
        if (is_null($key)) {
            return $this->_data;
        }
        return $this->_data[$key] ?? null;
    }

    public function __call(string $name, array $arguments)
    {
        if (preg_match('/^get/', $name)) {
            $field = preg_replace('/^get/', '', $name);
            return $this->getData($field);
        }
        throw new \Exception("Call to undefined method ". static::class ."::{$name}()");
    }

    public function __get(string $name)
    {
        return $this->getData($name);
    }
}