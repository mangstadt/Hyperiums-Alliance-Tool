<?php
require_once 'lib/bootstrap.php';
if (!Session::isLoggedIn()){
	header('Location: index.php');
	exit();
}

session_destroy();
header("Location: index.php?loggedout");