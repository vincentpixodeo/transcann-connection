<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Contracts;

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


    public function toArray(): array
    {
        $arrayData = [];

        foreach ($this->_data as $key => $item) {

            $arrayData[$key] = $item instanceof ObjectDataInterface ? $item->toArray() : $item;

        }

        return $arrayData;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call(string $name, array $arguments)
    {
        if (str_starts_with($name, 'get')) {
            $field = preg_replace('/^get/', '', $name);
            return $this->getData($field);
        }
        throw new \Exception("Call to undefined method ". static::class ."::{$name}()");
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->getData($name);
    }

}