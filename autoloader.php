<?php
spl_autoload_register(function ($class_name) {
    $preg_match = preg_match('/^WMS\\\/', $class_name);

    if (1 === $preg_match) {
        $class_name = preg_replace('/\\\/', '/', $class_name);

        $class_name = preg_replace('/^WMS\\//', '/', $class_name);

        if (file_exists(__DIR__ . $class_name . '.php')) {
            require_once(__DIR__ . $class_name . '.php');
        }
    }
});

if (!function_exists('dump')) {
    function dump(): void
    {
        foreach(func_get_args() as $item) {
            print_r($item);
        }
    }
}
if (!function_exists('dd')) {
    function dd(): void
    {
        foreach(func_get_args() as $item) {
            print_r($item);
        }

        die();
    }
}