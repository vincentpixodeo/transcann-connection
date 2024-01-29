<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Contracts;

use ArrayAccess;
use JsonSerializable;
use ReflectionException;
use WMS\Xtent\Helpers\Attribute;

class AbstractObjectData implements ObjectDataInterface, ArrayAccess, JsonSerializable
{
    /**
     * @var Attribute[]
     */
    protected static array $casts = [];

    protected array $_data;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->_initCasts();
        $this->setData($data);

    }

    /**
     * @param $class
     * @return void
     */
    private function _initCasts($class = null): void
    {
        is_null($class) && $class = static::class;

        if (isset(static::$casts[$class]))
            return;

        try {
            $reflector = new \ReflectionClass($class);
        } catch (ReflectionException $exception) {
            trigger_error($exception->getMessage(), E_USER_ERROR);
        }

        if ($parent = $reflector->getParentClass()) {
            $parentClassName = $parent->getName();
            $this->_initCasts($parentClassName);
            static::$casts[$class] = static::$casts[$parentClassName];
        } else {
            static::$casts[$class] = [];
        }

        $docs = $reflector->getDocComment();
        $pattern = "#@property\s*(([\w\\\]+)(\[])*)\s+\\$*([\w_]+)\s+([\w\s]*)#";

        if ($docs) {
            preg_match_all($pattern, $docs, $matches, PREG_SET_ORDER);

            foreach ($matches as $attribute) {
                list($full, $propertyInstance, $instance, $isArray, $property, $description) = $attribute;

                if (!in_array($instance, Attribute::SYSTEM_INSTANCES) && !class_exists($instance)) {
                    $message = "Please add full path of property " . $class . "::{$property}. Autoload property for " . $class;
                    if (!class_exists($instance = $reflector->getNamespaceName() . "\\" . $instance) && !class_exists($instance = "WMS\\Data\\" . $instance)) {
                        trigger_error($message, E_USER_ERROR);
                    }
                }
                static::$casts[$class][$property] = new Attribute($property, $instance, !empty($isArray), $description);
            }
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function _convertData(array $data): array
    {
        array_walk($data, function (&$val, $key) {
            /** @var Attribute $attribute */
            if ($attribute = (static::$casts[static::class][$key] ?? null)) {
                $val = $attribute->convertData($val);
            }
        });

        return $data;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data): static
    {
        $this->_data = $this->_convertData($data);
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function addData(array $data): static
    {
        $this->_data = array_merge($this->_data, $this->_convertData($data));
        return $this;
    }

    /**
     * @param string|null $key
     * @param $default
     * @return mixed
     */
    public function getData(string $key = null, $default = null): mixed
    {
        if (is_null($key)) {
            return $this->_data;
        }
        return $this->_data[$key] ?? $default;
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function __unserialize(array $data): void
    {
        $this->setData($data);
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function ($value) {
            return match (true) {
                $value instanceof ObjectDataInterface => $value->toArray(),
                $value instanceof \DateTime => $value->format('Y-m-d H:i:s'),
                true => $value
            };
        }, $this->_data);
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
        throw new \Exception("Call to undefined method " . static::class . "::{$name}()");
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->getData($name);
    }

    /**
     * @param string $name
     * @param $value
     * @return void
     */
    public function __set(string $name, $value): void
    {
        $this->addData([$name => $value]);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->_data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->getData($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        array_merge($this->_data, $this->_convertData([$offset => $value]));
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->_data[$offset]);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}