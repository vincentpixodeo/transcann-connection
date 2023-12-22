<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Data;


use WMS\Xtent\Contracts\ObjectDataInterface;

class Collection implements ObjectDataInterface, \ArrayAccess
{
    protected array $_items = [];

    public function __construct(array $data = [])
    {
        $this->_items = $data;
    }

    public function setData(array $data): static
    {
        $this->_items = $data;
        return $this;
    }

    public function addData(array $data): static
    {
        $this->_items = array_merge($this->_items, $data);
        return $this;
    }

    public function getData(string $key = null, $default = null): mixed
    {
        if (is_null($key)) {
            return $this->_items;
        }

        return $this->_items[$key] ?? $default;
    }

    public function toArray(): array
    {
        return array_map(function ($value) {
            return $value instanceof ObjectDataInterface ? $value->toArray() : $value;
        }, $this->_items);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->_items[$offset]);
    }

    public function offsetGet(mixed $offset)
    {
        return $this->_items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->_items[] = $value;
        } else {
            $this->_items[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->_items[$offset]);
    }
}