<?php
/*
 * This script will drop all tables in the database.
 */

//logged in?
session_start();
if (!isset($_SESSION['loggedIn'])){
	header("Location: index.php");
	exit();
}

require_once '../lib/bootstrap.php';
use db\HypToolsDao;

header("Content-Type: text/plain");

echo "Dropping all tables...\n";
flush();
$dao = new HypToolsDao();
$dao->dropAllTables();
echo "Done.";