<?php
session_start();

if (!isset($_SESSION['loggedIn'])){
	$password = @$_POST["password"];
	if ($password !== null){
		$correctPw = isset($_SERVER['admin_pw']) ? $_SERVER['admin_pw'] : 'glass';
		if ($password == $correctPw){
			$_SESSION['loggedIn'] = true;
		}
	}
}

$loggedOut = false;
if (isset($_GET['logout'])){
	$loggedOut = true;
	session_destroy();
	unset($_SESSION['loggedIn']);
}

?>

<html><body>
	<?php
	if (isset($_SESSION['loggedIn'])):
		?>
		<a href="update-alliances.php">Update alliances</a> - Update the alliance info in the DB from HAPI alliance data files located in this dir.<br />
		<a href="index.php?logout">Logout</a> - Logout.<br />
		<br />
		<a style="background-color:#f99" href="wipe-db.php" onclick="return confirm('Are you sure you want to delete the entire database?  This cannot be undone.')">Wipe Database</a> - Wipe the database if you need to rebuild the schema.<br />
		<?php
	else:
		if ($loggedOut):
			?>Logged out.<br /><?php
		endif;
		?>
		Enter passwords:
		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"])?>" method="POST">
		<input type="text" name="password" />
		<input type="submit" />
		</form>
		<?php
	endif;
	?>
</body></html>