<?php
require_once 'lib/bootstrap.php';
session_start();
$hapi = @$_SESSION['hapi'];
if ($hapi == null){
	header('Location: index.php');
	exit();
}

//$hapi->logout();
session_destroy();
header("Location: index.php?loggedout");