<?php
require_once 'lib/bootstrap.php';
use db\HypToolsDao;

//has the player logged in?
session_start();
$hapi = @$_SESSION['hapi'];
if ($hapi == null){
	header('Location: index.php');
	exit();
}

$player = $_SESSION['player'];

$dao = new HypToolsDao();

//user has requested to join an alliance
$joinTag = @$_POST["joinTag"];
$joinTagError = null;
$joinTagSuccess = null;
if ($joinTag !== null){
	$joinTag = trim(trim($joinTag), "]["); //remove brackets
	if ($joinTag != ""){
		$alliance = $dao->selectAlliance($joinTag);
		if ($alliance != null){
			$requested = $dao->selectAlreadyRequested($player, $alliance);
			if ($requested){
				$joinTagError = "You already sent a request or already belong to the alliance.";
			} else {
				if ($player->id == $alliance->president->id){
					//the user is the president of the alliance, give him full access
					$dao->insertPresidentPermission($player, $alliance);
					$joinTagSuccess = "Hello Mr. President.";
				} else {
					$dao->insertAllianceJoinRequest($player, $alliance);
					$joinTagSuccess = "Join request sent.";
				}
			}
		} else {
			$joinTagError = "Alliance does not exist.";
		}
	} else {
		$joinTagError = "Invalid tag.";
	}
}

//the alliances the player belongs to
$accepted = $dao->selectAcceptedPermissions($player);

//the pending alliance requests
$pendingRequests = $dao->selectPendingPermissions($player);

?>
<!DOCTYPE html>
<html lang="en">

	<head>

		<meta charset="utf-8" />
		<title>Hyperiums Alliance Tools</title>

		<link rel="stylesheet" href="css/hat.less" type="text/less" />
		
		<link  href="http://fonts.googleapis.com/css?family=Metrophobic:regular" rel="stylesheet" type="text/css" />

	</head>


	<body>
		
		<div id="logo"></div>
		
		<div id="main">
			
			<div id="header">
				<div class="h100"></div>
			</div>
			
			<div id="content" style="color:white">
				<form method="post" action="home.php">
					Alliances: 
					<?php
					if (count($accepted) == 0):
						?><i>none</i><?php
					else:
						foreach ($accepted as $a):
							?><a href="alliance.php?id=<?php echo urlencode($a->alliance->id) ?>">[<?php echo htmlspecialchars($a->alliance->tag) ?>]</a><?php
						endforeach;
					endif;
					?>
					| join:
					<input type="text" name="joinTag" size="5" value="<?php echo htmlspecialchars($joinTag)?>" />
					<input type="submit" value="Send" />
					<?php
					if ($joinTagError != null):
						?><font color="red"><?php echo htmlspecialchars($joinTagError)?></font><?php
					elseif ($joinTagSuccess != null):
						?><b><?php echo htmlspecialchars($joinTagSuccess)?></b><?php
					endif;
					?>
				</form>
				<br /><a href="logout.php">Logout</a><br /><br />
				<?php
				if (count($pendingRequests) > 0):
					?><div><b><i>Pending join requests:</i></b><br /><?php
					foreach ($pendingRequests as $p):
						?><b><?php echo htmlspecialchars($p->alliance->tag)?></b> - requested on <?php echo htmlspecialchars($p->requestDate->format("Y-m-d G:i T"))?>.<br /><?php
					endforeach;
					?></div><?php
				endif;
				?>
			</div>
			
		</div>

		<div id="javascript">
			<script type="text/javascript" src="js/less-1.1.3.min.js"></script>
		</div>

	</body>

</html>