<?php
require_once 'lib/bootstrap.php';
use db\HypToolsMySqlDao;
use db\HypToolsMockDao;
use db\JoinLog;

//has the player logged in?
if (!Session::isLoggedIn()){
	header('Location: index.php');
	exit();
}

//init DAO
$player = Session::getPlayer();
$mock = Session::isMockEnabled();
$dao = $mock ? new HypToolsMockDao($player->game) : new HypToolsMySqlDao($player->game);

//was a tag specified?
$allianceTag = @$_REQUEST['tag'];
if ($allianceTag == null){
	header('Location: home.php');
	exit();
}

//get the specified alliance
$alliance = $dao->selectAllianceByTag($allianceTag);
if ($alliance == null){
	//alliance does not exist
	header('Location: home.php');
	exit();
}

//get the permissions the player has within this alliance
$playerPermissions = $dao->selectPermissionsByPlayerAndAlliance($player, $alliance);
if ($playerPermissions == null){
	//player does not belong to this alliance
	header('Location: home.php');
	exit();
}

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
					Hello, <b><?php echo htmlspecialchars($player->name)?>!</b>
				</div>
			
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
							?><a href="submit.php?tag=<?php echo urlencode($a->alliance->tag) ?>">[<?php echo htmlspecialchars($a->alliance->tag) ?>]</a><?php
						endfor;
					endif;
					?>
				</div>
				
				<div align="center">
					<div><b>[<?php echo htmlspecialchars($alliance->tag)?>]</b></div>
					<?php
					$links = array();
					if ($playerPermissions->permSubmit):
						$links[] = '<b><a href="submit.php?tag=' . urlencode($alliance->tag) . '">Submit Data</a></b>';
					endif;
					if ($playerPermissions->permView):
						$links[] = '<a href="view.php?tag=' . urlencode($alliance->tag) . '">View Alliance Data</a>';
					endif;
					if ($playerPermissions->permAdmin):
						$links[] = '<a href="admin.php?tag=' . urlencode($alliance->tag) . '">Admin</a>';
					endif;
					echo implode(" | ", $links);
					?>
				</div>
				
				<div class="block">
					<h1>Fleets</h1>
					<div>
						<p>The following fleet data will be submitted to the [<b><?php echo htmlspecialchars($alliance->tag)?></b>] alliance.</p>
						<b>Coming soon...</b>
					</div>
				</div>
			</div>
			
		</div>

		<div id="javascript">
			<script type="text/javascript" src="js/less-1.1.3.min.js"></script>
		</div>

	</body>

</html>