<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent;

use WMS\Xtent\Contracts\ClientInterface;
use WMS\Xtent\Contracts\RequestActionInterface;
use WMS\Xtent\Http\Curl;
use WMS\Xtent\Http\Exception;

/**
 * @method static ClientInterface client()
 * @method static array|mixed|null config(string|null $key = null, $default = null)
 * @method static WMSAuthentication authentication()
 * @method static object action(string $className, array $params = [])
 */
class WmsXtentService
{
    static protected ?WmsXtentService $_instance = null;
    protected ?WMSAuthentication $authentication = null;
    protected array $configs;

    public function __construct()
    {
        if (is_null(static::$_instance)) {
            $this->configs = include __DIR__ . '/config.php';
        } else {
            $this->configs = static::$_instance->getConfig();
        }
    }

    /**
     * @param string|null $key
     * @param null $default
     * @return array|mixed|null
     */
    function getConfig(string $key = null, $default = null): mixed
    {
        if (is_null($key))
            return $this->configs;
        return $this->configs[$key] ?? $default;
    }

    function getClient(): ClientInterface
    {
        return new Curl($this->getConfig('baseUrl'));
    }

    /**
     * @param string $path
     * @return string
     */
    function storagePath(string $path = ''): string
    {
        $root = trim($this->getConfig('storage', __DIR__ . '/storage'), '\\/');
        $path = trim($path ?? '', '\\/');

        $path = preg_replace('/\\\/', '/', $path);

        $trees = explode('/', $path);

        $path = $root;

        foreach ($trees as $tree) {
            $path .= "/{$tree}";
            if (!file_exists($path)) {
                mkdir($path);
            }
        }
        return $path;
    }

    /**
     * @return WMSAuthentication
     */
    public function getAuthentication(): WMSAuthentication
    {
        if (is_null($this->authentication)) {
            $config = $this->getConfig();
            $this->authentication = new WMSAuthentication($config['accessId'], $config['userId'], $config['password']);
        }
        return $this->authentication;
    }

    /**
     * @param string $className
     * @param array $params
     * @return object
     * @throws \Exception
     */
    public static function getAction(string $className, array $params = []): object
    {
        $action = new $className(...$params);

        if (!$action instanceof RequestActionInterface) {
            throw new \Exception($className . ' must be instance of ' . RequestActionInterface::class);
        }

        return $action;
    }

    /**
     * @return WmsXtentService
     */
    static function instance(): WmsXtentService
    {
        if (is_null(static::$_instance)) {
            static::$_instance = new WmsXtentService();
        }
        return static::$_instance;
    }

    /**
     * Refresh to Clear Memory
     * @return void
     */
    public function refresh(): void
    {
        unset($this->authentication);
        $this->authentication = null;
    }

    /**
     * @throws Exception
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $method = 'get' . ucfirst($name);
        if (method_exists(static::class, $method)) {
            return static::instance()->{$method}(...$arguments);
        }
        throw new Exception("Call to undefined method " . static::class . "::{$name}()");
    }

}