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
	echo "No data file found.";
	exit();
}

echo "Working, please wait...\n";
flush();
$dao = new HypToolsDao();
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