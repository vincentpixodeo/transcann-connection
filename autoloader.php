<?php


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

if (!function_exists('dump')) {
    function dump(): void
    {
        foreach (func_get_args() as $item) {
            print_r($item);
        }
    }
}
if (!function_exists('dd')) {
    function dd(): void
    {
        foreach (func_get_args() as $item) {
            print_r($item);
        }

        die();
    }
}