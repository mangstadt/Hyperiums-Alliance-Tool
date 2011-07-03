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

$dao = new HypToolsDao($player->game);

//user has requested to cancel a pending join request
$cancelJoinRequest = @$_POST['cancelJoinRequest'];
if ($cancelJoinRequest !== null){
	$joinRequest = $dao->selectJoinRequestById($cancelJoinRequest);
	if ($joinRequest != null && $joinRequest->player->id == $player->id){
		$dao->deleteJoinRequest($joinRequest->id);
	}
}

//user has requested to join an alliance
$joinTag = @$_POST["joinTag"];
if ($joinTag !== null){
	$joinTag = trim(trim($joinTag), "]["); //remove brackets
	if ($joinTag != ""){
		$alliance = $dao->selectAllianceByTag($joinTag);
		if ($alliance != null){
			if ($dao->hasPlayerMadeJoinRequest($player, $alliance)){
				$joinTagError = "You already sent an authentication request to join the [{$alliance->tag}] alliance.";
			} else {
				if ($dao->doesPlayerBelongToAlliance($player, $alliance)){
					$joinTagError = "You already belong to the [{$alliance->tag}] alliance.";
				}
				else {
					if ($player->id == $alliance->president->id){
						//the user is the president of the alliance, give him full access
						$dao->insertPresidentPermission($player, $alliance);
						$joinTagSuccess = "Hello Mr. President.";
					} else {
						$dao->insertJoinRequest($player, $alliance);
						$joinTagSuccess = "Authentication request sent to [{$alliance->tag}].";
					}
					$joinTag = "";
				}
			}
		} else {
			$joinTagError = "Alliance <b>[$joinTag]</b> does not exist.";
		}
	} else {
		$joinTagError = "Invalid tag.";
	}
}

//get the player's pending join requests
$playerJoinRequests = $dao->selectJoinRequestsByPlayer($player);

//get the alliances the player belongs to
$playerAlliances = $dao->selectPermissionsByPlayer($player);

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
			
				<div>
					<a href="home.php">Home</a>
					<a href="logout.php">Logout</a>
				</div>
				
				<div style="border-bottom:3px solid #fff; padding-bottom:5px">
					Alliances: 
					<?php
					if (count($playerAlliances) == 0):
						?><i>none</i><?php
					else:
						for ($i = 0; $i < count($playerAlliances); $i++):
							$a = $playerAlliances[$i];
							if ($i > 0) echo ' | ';
							?><a href="alliance.php?tag=<?php echo urlencode($a->alliance->tag) ?>">[<?php echo htmlspecialchars($a->alliance->tag) ?>]</a><?php
						endfor;
					endif;
					?>
				</div>
				
				<div class="block">
					<h1>Alliance Authentication</h1>
					<div>
						<p>Authenticate with your alliance to begin submitting your fleet, trading, and infiltration data.
						Note that authentication requires approval from the alliance president.
						Alliance presidents are automatically approved.</p>
						
						<form action="home.php" method="post">
							<b>Tag:</b> <input type="text" name="joinTag" value="<?php echo isset($joinTagSuccess) ? '' : htmlspecialchars($joinTag)?>"/>
							<input type="submit" value="Send Auth Request" class="button" />
							<?php
							if (isset($joinTagError)):
								echo "<span style=\"color:red\"><b>$joinTagError</b></span>";
							elseif (isset($joinTagSuccess)):
								echo "<b>$joinTagSuccess</b>";
							endif;
							?>
						</form>
						
						<?php
						if (count($playerJoinRequests) > 0):
							?>
							<h2>Pending Requests:</h2>
							<table>
							<?php
							foreach ($playerJoinRequests as $r):
								?>
								<tr>
									<td>[<b><?php echo htmlspecialchars($r->alliance->tag)?></b>] - requested on <?php echo htmlspecialchars($r->requestDate->format("Y-m-d G:i T"))?>.</td>
									<td><form method="post" action="home.php"><input type="hidden" name="cancelJoinRequest" value="<?php echo htmlspecialchars($r->id)?>" /><input type="submit" value="Cancel" class="button" /></form></td>
								</tr>
								<?php
							endforeach;
							?>
							</table>
							<?php
						endif;
						?>
					</div>
				</div>
			</div>
			
		</div>

		<div id="javascript">
			<script type="text/javascript" src="js/less-1.1.3.min.js"></script>
		</div>

	</body>

</html>