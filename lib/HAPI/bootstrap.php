<?php
namespace HAPI;

spl_autoload_register(function ($class) {
    if (substr($class, 0, strlen(__NAMESPACE__)) == __NAMESPACE__) {
        $class = substr($class, strlen(__NAMESPACE__) + 1);
        $path = str_replace('\\', '/', $class);
        $path = __DIR__ . '/' . $path . '.php';
        if (file_exists($path)){
        	require_once $path;
        }
    }
});