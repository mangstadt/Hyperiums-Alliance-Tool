<?php
/*
 * This script automatically includes all classes.
 */

require_once __DIR__ . '/PHP-HAPI-0.3.2.phar';

spl_autoload_register(function ($class) {
	$path = str_replace('\\', '/', $class); //replace back-slashes with forward-slashes
	$path = __DIR__ . "/$path.php";
	if (file_exists($path)){
		require_once $path;
	}
});