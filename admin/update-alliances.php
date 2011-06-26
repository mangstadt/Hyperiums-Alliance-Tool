<?php
/*
 * This script will upsert each alliance in an alliance data file into the database.
 * It expects there to be only one alliance data file in this directory and the file must have the extension ".txt.gz".
 */

//password protect this script
$password = @$_POST["password"];
$correctPw = isset($_SERVER['admin_pw']) ? $_SERVER['admin_pw'] : 'glass';
if ($password === null || $password != $correctPw){
	?>
	<html><body>
	Enter passwords:
	<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"])?>" method="POST">
	<input type="text" name="password" />
	<input type="submit" />
	</form>
	</body></html>
	<?php
	exit();
}

require_once '../lib/bootstrap.php';
use HAPI\HAPI;
use HAPI\Parsers\AllianceParser;
use db\HypToolsDao;

header("Content-Type: text/plain");

//treats the first ".txt.gz" file it finds in this directory as the alliance data file
$handler = opendir(__DIR__);
$dataFile = null;
while ($file = readdir($handler)) {
	$ext = substr($file, -7);
	if ($ext == ".txt.gz"){
		$dataFile = $file;
		break;
	}
}

if ($dataFile == null){
	echo "Error: No data file found.";
	exit();
}

echo "Working, please wait...\n";
flush();

//get the game name from the file name
$gameName = null;
if (preg_match("/^(.*?)-/", $dataFile, $matches)){
	$gameName = $matches[1];
} else {
	echo "Error: Game name not found in file name.";
	exit();
}

//make sure the game exists
$games = HAPI::getAllGames();
$game = null;
foreach ($games as $g){
	if (strcasecmp($g->getName(), $gameName) == 0){
		$game = $g;
		break;
	}
}
if ($game == null){
	echo "Error: Game \"$gameName\" not found.";
	exit();
}

//init DAO
$dao = new HypToolsDao();
$game = $dao->upsertGame($game->getName(), $game->getDescription());
$dao->setGame($game);

$dao->beginTransaction();
try{
	$parser = new AllianceParser(__DIR__ . "/$dataFile");
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
echo "Processed $num alliances.";