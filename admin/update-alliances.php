<?php
/*
 * This script will upsert each alliance with data from alliance data files.
 * It expects there to be alliance data files in this directory with file names in the format "<gameName>-<yyyymmdd>-alliance.txt.gz".  For example: "Hyperiums6-20110625-alliances.txt.gz"
 */

//logged in?
session_start();
if (!isset($_SESSION['loggedIn'])){
	header("Location: index.php");
	exit();
}

require_once '../lib/bootstrap.php';
use HAPI\HAPI;
use HAPI\Parsers\AllianceParser;
use db\HypToolsDao;

header("Content-Type: text/plain");

echo "Working, please wait...\n";
flush();

//find the data files
$handler = opendir(__DIR__);
$dataFiles = array();
while ($file = readdir($handler)) {
	if (preg_match("/^(.*?)-(.*?)-alliances\\.txt\\.gz\$/", $file, $matches)){
		$gameName = $matches[1];
		$dataFiles[] = array($gameName, $file);
	}
}

if (count($dataFiles) == 0){
	echo "Error: No data files found.";
	exit();
}

//get all Hyperiums games
$games = HAPI::getAllGames();

//init the DAO
$dao = new HypToolsDao();

foreach ($dataFiles as $dataFile){
	$gameName = $dataFile[0];
	$file = $dataFile[1];
	
	//make sure the game exists
	$game = null;
	foreach ($games as $g){
		if (strcasecmp($g->getName(), $gameName) == 0){
			$game = $g;
			break;
		}
	}
	if ($game == null){
		echo "Error: Could not process \"$file\". Game with name \"$gameName\" does not exist.\n";
		continue;
	}
	
	//set up DAO
	$game = $dao->upsertGame($game->getName(), $game->getDescription());
	$dao->setGame($game);
	
	$dao->beginTransaction();
	try{
		$parser = new AllianceParser(__DIR__ . "/$file");
		$num = 0;
		while ($alliance = $parser->next()){
			$dao->upsertAlliance($alliance->getTag(), $alliance->getName(), $alliance->getPresident());
			$num++;
		}
		$dao->commit();
	} catch (Exception $e){
		$dao->rollBack();
		throw $e;
	}
	echo "Processed $num alliances in file \"$file\".\n";
}