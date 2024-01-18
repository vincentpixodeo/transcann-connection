<?php
require_once __DIR__ . '/../../../master.inc.php';

if (file_exists($path = __DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    spl_autoload_register(function ($class_name) {
        $preg_match = preg_match('/^WMS\\\Xtent\\\/', $class_name);

        if (1 === $preg_match) {
            $class_name = preg_replace('/\\\/', '/', $class_name);

            $class_name = preg_replace('/^WMS\\/Xtent\\//', '/', $class_name);

            $path = __DIR__ . '/src/' . $class_name . '.php';

            if (file_exists($path)) {
                require_once($path);
            }
        }
    });
    require_once __DIR__ . '/helper/functions.php';
}

