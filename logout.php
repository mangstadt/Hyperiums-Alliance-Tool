<?php
require_once __DIR__ . '/lib/PHP-HAPI-0.2.0.phar';
session_start();
$hapi = @$_SESSION['hapi'];
if ($hapi == null){
	header('Location: index.php');
	exit();
}

$hapi->logout();
session_destroy();
header("Location: index.php?loggedout");