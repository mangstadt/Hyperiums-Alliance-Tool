<?php
/**
 * Contains environment-specific settings.&nbsp;
 * This should ideally be the only file that changes when running under different environments.
 * @author mangstadt
 */
class Env{
	
	/**
	 * Where all writable files go.
	 * @var string
	 */
	public static $cacheDir;
	
	/**
	 * Creates a database connection.
	 * @return PDO the database connection
	 */
	public static function dbConnect(){
		$host = $_SERVER['db_host'];
		$host = substr($host, strpos($host, ":")+1); //looks like: "localhost:/tmp/mysql/daniela.sock"
		$name = $_SERVER['db_name'];
		$user = $_SERVER['db_user'];
		$pass = $_SERVER['db_pass'];
		$db = new PDO("mysql:unix_socket=$host;dbname=$name", $user, $pass);
		return $db;
	}
}

Env::$cacheDir = __DIR__ . '/../cache';
