<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Contracts;

class AbstractObjectData implements ObjectDataInterface
{
    /**
     * @var Attribute[]
     */
    protected static array $casts = [];

    protected array $_data;

    /**
     * @throws \Exception
     */
    public function __construct(array $data = [])
    {
        $this->_initCasts();
        $this->setData($data);

    }

    private function _initCasts(): void
    {
        if (isset(static::$casts[static::class]))
            return;

        $reflector = new \ReflectionClass(static::class);

        $docs = $reflector->getDocComment();
        $pattern = "#@property\s*(([\w\\\]+)(\[])*)\s+([\w_]+)\s+([\w\s]*)#";
        static::$casts[static::class] = [];
        if ($docs) {
            preg_match_all($pattern, $docs, $matches, PREG_SET_ORDER);
            foreach ($matches  as $attribute) {
                list($full, $propertyInstance, $instance, $isArray, $property, $description) = $attribute;

                if (! in_array($instance, Attribute::SYSTEM_INSTANCES) && !class_exists($instance)) {
                    $message = "Please add full path of property ".static::class."::{$property}. Autoload property for ".static::class." at file {$reflector->getFileName()}";
                    if (!class_exists($reflector->getNamespaceName()."\\".$instance) && !class_exists("WMS\\Data\\".$instance)) {
                        throw new \Exception($message);
                    }
                }
                static::$casts[static::class][$property] = new Attribute($property, $instance, !empty($isArray), $description);
            }
        }
    }
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

    public function setData(array $data): static
    {
        $this->_data = $this->_convertData($data);
        return $this;
    }

    public function addData(array $data): static
    {
        $this->_data = array_merge($this->_data, $this->_convertData($data));
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
        return array_map(function ($value) {
            return $value instanceof ObjectDataInterface ? $value->toArray() : $value;
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