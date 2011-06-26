<?php
/*
 * This script will drop all tables in the database.
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
use db\HypToolsDao;

header("Content-Type: text/plain");

echo "Dropping all tables...\n";
flush();
$dao = new HypToolsDao();
$dao->dropTables();
echo "Done.";