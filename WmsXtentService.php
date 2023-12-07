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
    static protected $_instance;
    protected array $configs;
    protected $authentication;
    public function __construct()
    {
        $this->configs = include __DIR__.'/config.php';

    }

    /**
     * @param $key
     * @param $default
     * @return array|mixed|null
     */
    function getConfig($key = null, $default = null)
    {
        if (is_null($key))
            return $this->configs;
        return $this->configs[$key] ?? $default;
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
