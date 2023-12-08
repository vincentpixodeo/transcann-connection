<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS;

use WMS\Contracts\RequestActionInterface;

require_once('autoloader.php');
class WmsXtentService
{
    const ROOT_DIR = __DIR__;

    static protected ?WmsXtentService $_instance = null;
    protected array $configs;
    protected ?WMSAuthentication $authentication = null;

    public function __construct()
    {
        $this->configs = include __DIR__.'/config.php';

    }

    /**
     * @param $key
     * @param $default
     * @return array|mixed|null
     */
    function getConfig($key = null, $default = null): mixed
    {
        if (is_null($key))
            return $this->configs;
        return $this->configs[$key] ?? $default;
    }

    /**
     * @param string $path
     * @return string
     */
    function storagePath(string $path = ''): string
    {
        $path = trim($this->getConfig('storage', 'storage'), '\\/').'/'.trim($path ?? '', '\\/');

        $path = preg_replace('/\\\/', '/', $path);

        $trees = explode('/', $path);

        $path = self::ROOT_DIR;

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
    public function getAction(string $className, array $params = []): object
    {
        $action = new $className(...$params);

        if (!$action instanceof RequestActionInterface) {
            throw new \Exception($className. ' must be instance of '. RequestActionInterface::class);
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
}
