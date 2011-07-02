<?php
/*
 * This file creates a PDO object and initializes the database.
 * The PDO variable name must be "$db".
 * Edit this file for your local database environment.
 */

//pagodabox
$host = $_SERVER['db_host'];
$host = substr($host, strpos($host, ":")+1);
$name = $_SERVER['db_name'];
$user = $_SERVER['db_user'];
$pass = $_SERVER['db_pass'];

$db = new \PDO("mysql:unix_socket=$host;dbname=$name", $user, $pass);
