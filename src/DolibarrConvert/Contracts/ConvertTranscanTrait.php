<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Contracts;

use Exception;
use WMS\Xtent\Contracts\ObjectDataInterface;

trait ConvertTranscanTrait
{
    abstract function getMapAttributes(): array;

    abstract function getAppendAttributes(): array;

    abstract function getTranscanInstance(): string|ObjectDataInterface;

    public function isTranscanInstance(ObjectDataInterface $instance): bool
    {
        $transcanInstance = $this->getTranscanInstance();
        if ($instance instanceof $transcanInstance) {
            return true;
        }
        throw new Exception('the instance not is a instance of ' . $transcanInstance);
    }

    public function createFromTranscan(ObjectDataInterface $item): static
    {
        $instance = new static();

        if ($this->isTranscanInstance($item)) {
            foreach ($this->getMapAttributes() as $productAttribute => $transcanAttribute) {
                $value = $item->getData($transcanAttribute);
                $value instanceof ObjectDataInterface && $value = $value->toArray();
                $instance->addData([$productAttribute => $value]);
            }
        }
        return $instance;
    }

    public function convertToTranscan(): ObjectDataInterface
    {
        /** @var ObjectDataInterface $this */
        $instance = $this->getTranscanInstance();

        is_string($instance) && $instance = new $instance();

        foreach ($this->getMapAttributes() as $productAttribute => $transcanAttribute) {
            $value = $this->getData($productAttribute);
            $value instanceof ObjectDataInterface && $value = $value->toArray();
            $instance->addData([$transcanAttribute => $value]);
        }

        $instance->addData($this->getAppendAttributes());
        return $instance;
    }
}