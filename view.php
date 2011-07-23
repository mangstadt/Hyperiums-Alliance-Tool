<?php
require_once 'lib/bootstrap.php';
use db\Fleet;
use db\HypToolsMySqlDao;
use db\HypToolsMockDao;
use db\JoinLog;

//has the player logged in?
if (!Session::isLoggedIn()){
	header('Location: index.php');
	exit();
}

//was a tag specified?
$allianceTag = @$_REQUEST['tag'];
if ($allianceTag == null){
	header('Location: home.php');
	exit();
}

//init DAO
$player = Session::getPlayer();
$mock = Session::isMockEnabled();
$dao = $mock ? new HypToolsMockDao($player->game) : new HypToolsMySqlDao($player->game);

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
} else if (!$playerPermissions->permView){
	//player does not have permission to view the view page
	header('Location: submit.php?tag=' . urlencode($alliance->tag));
	exit();
}

//get the alliances the player belongs to
$playerAlliances = $dao->selectPermissionsByPlayer($player);

//get the fleet reports of all alliance members
$fleets = $dao->selectFleetsByAlliance($alliance);

//sum up all fleets
$fleetTotals = new Fleet();
foreach ($fleets as $fleet){
	$fleetTotals->azterkScouts += $fleet->azterkScouts;
	$fleetTotals->azterkDestroyers += $fleet->azterkDestroyers;
	$fleetTotals->azterkBombers += $fleet->azterkBombers;
	$fleetTotals->azterkCruisers += $fleet->azterkCruisers;
	$fleetTotals->azterkArmies += $fleet->azterkArmies;
	
	$fleetTotals->humanScouts += $fleet->humanScouts;
	$fleetTotals->humanDestroyers += $fleet->humanDestroyers;
	$fleetTotals->humanBombers += $fleet->humanBombers;
	$fleetTotals->humanCruisers += $fleet->humanCruisers;
	$fleetTotals->humanArmies += $fleet->humanArmies;
	
	$fleetTotals->xillorScouts += $fleet->xillorScouts;
	$fleetTotals->xillorDestroyers += $fleet->xillorDestroyers;
	$fleetTotals->xillorBombers += $fleet->xillorBombers;
	$fleetTotals->xillorCruisers += $fleet->xillorCruisers;
	$fleetTotals->xillorArmies += $fleet->xillorArmies;
}

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
						$links[] = '<a href="submit.php?tag=' . urlencode($alliance->tag) . '">Submit Data</a>';
					endif;
					if ($playerPermissions->permView):
						$links[] = '<b><a href="view.php?tag=' . urlencode($alliance->tag) . '">View Alliance Data</a></b>';
					endif;
					if ($playerPermissions->permAdmin):
						$links[] = '<a href="admin.php?tag=' . urlencode($alliance->tag) . '">Admin</a>';
					endif;
					echo implode(" | ", $links);
					?>
				</div>
				
				<div class="block">
					<h1>Fleet Status Report</h1>
					<?php 
					if (count($fleets) == 0):
						?><i>No reports submitted.</i><?php
					else:
						?>
						<h2>[<?php echo htmlspecialchars($alliance->tag)?>] Total Fleet Power</h2>
						<div align="center" style="border-bottom:5px solid #fff; padding-bottom:15px;">
							<table cellspacing="10">
								<tr>
									<td><?php echo fleetTable($fleetTotals)?></td>
									<td><?php echo avgPTable($fleetTotals)?></td>
								</tr>
							</table>
						</div>
						
						<h2>Fleets by Player</h2>
						<table width="100%" cellpadding="10">
							<?php
							for ($i = 0; $i < count($fleets); $i++):
								$fleet = $fleets[$i];
								
								if ($i % 2 == 0):
									?><tr><?php
								endif;
								
								?>
								<td>
									<b><?php echo htmlspecialchars($fleet->player->name)?></b><br />
									Last Submission: <?php echo htmlspecialchars($fleet->submitDate->format("Y-m-d G:i T"))?>
									<?php echo fleetTable($fleet)?>
									<?php echo avgPTable($fleet)?>
								</td>
								
								<?php
								if ($i % 2 == 1):
									?></tr><?php
								endif;
							endfor;
							if ($i % 2 == 0):
								?></tr><?php
							endif;
							?>
						</table>
						<?php
					endif;
					?>
				</div>
			</div>
			
		</div>

		<div id="javascript">
			<script type="text/javascript" src="js/less-1.1.3.min.js"></script>
		</div>

	</body>

</html>

<?php
/**
 * Builds the bars used to represent AvgP.
 * @param integer $avgP the avgP
 * @return string the HTML table
 */
function avgPBars($avgP){
	$html = "";
	$blocks = array(
		array(500000, 'pow22'),
		array(50000, 'pow21'),
		array(5000, 'pow20')
	);
	$mod = $avgP;
	
	$html .= '<table cellspacing="1" cellpadding="1" border="0"><tr>';
	foreach ($blocks as $block){
		$count = floor($mod / $block[0]);
		for ($i = 0; $i < $count; $i++){
			$html .= '<td class="pow ' . $block[1] . '"></td>';
		}
		$mod = $mod % $block[0];
	}
	$html .= '</tr></table>';
	
	return $html;
}

/**
 * Builds the table used to display AvgP.
 * @param Fleet $fleet the fleet
 * @return string the HTML table
 */
function avgPTable(Fleet $fleet){
	$spaceAvgP = 0;
	$groundAvgP = 0;
	
	$spaceAvgP += $fleet->azterkScouts * AvgP::AZTERK_SCOUT;
	$spaceAvgP += $fleet->azterkDestroyers * AvgP::AZTERK_DESTROYER;
	$spaceAvgP += $fleet->azterkBombers * AvgP::AZTERK_BOMBER;
	$spaceAvgP += $fleet->azterkCruisers * AvgP::AZTERK_CRUISER;
	$groundAvgP += $fleet->azterkArmies * AvgP::AZTERK_ARMY;

	$spaceAvgP += $fleet->humanScouts * AvgP::HUMAN_SCOUT;
	$spaceAvgP += $fleet->humanDestroyers* AvgP::HUMAN_DESTROYER;
	$spaceAvgP += $fleet->humanBombers * AvgP::HUMAN_BOMBER;
	$spaceAvgP += $fleet->humanCruisers * AvgP::HUMAN_CRUISER;
	$groundAvgP += $fleet->humanArmies * AvgP::HUMAN_ARMY;

	$spaceAvgP += $fleet->xillorScouts * AvgP::XILLOR_SCOUT;
	$spaceAvgP += $fleet->xillorDestroyers * AvgP::XILLOR_DESTROYER;
	$spaceAvgP += $fleet->xillorBombers * AvgP::XILLOR_BOMBER;
	$spaceAvgP += $fleet->xillorCruisers * AvgP::XILLOR_CRUISER;
	$groundAvgP += $fleet->xillorArmies * AvgP::XILLOR_ARMY;
	
	$spaceAvgPText = $spaceAvgP;
	if ($spaceAvgP > 1000000) {
		$spaceAvgPText = number_format($spaceAvgP / 1000000, 1) . "M";
	} else if ($spaceAvgP > 1000) {
		$spaceAvgPText = number_format($spaceAvgP / 1000, 1) . "K";
	}
	
	$groundAvgPText = $groundAvgP;
	if ($groundAvgP > 1000000) {
		$groundAvgPText = number_format($groundAvgP / 1000000, 1) . "M";
	} else if ($groundAvgP > 1000) {
		$groundAvgPText = number_format($groundAvgP / 1000, 1) . "K";
	}
	
	ob_start();
	?>
	<table cellspacing="1" cellpadding="1" border="0">
		<tr>
			<td>Space AvgP:</td>
			<td><?php echo avgPBars($spaceAvgP)?></td>
			<td><?php echo $spaceAvgPText?></td>
		</tr>
		<tr>
			<td>Ground AvgP:</td>
			<td><?php echo avgPBars($groundAvgP)?></td>
			<td><?php echo $groundAvgPText?></td>
		</tr>
	</table>
	<?php
	return ob_get_clean();
}

/**
 * Builds the table that shows unit by unit breakdown of a fleet.
 * @param $fleet the fleet
 * @return string the HTML table
 */
function fleetTable(Fleet $fleet){
	ob_start();
	?>
	<table cellspacing="1" cellpadding="0" border="0">
		<tbody>
			<tr>
				<td width="180"></td>
				<td width="50" class="hc"><font color="#AAAA77"
					face="verdana,arial" size="1"><img
					src="img/race_human.gif" border="0"></font></td>
				<td width="50" class="hc"><font color="#AAAA77"
					face="verdana,arial" size="1"><img
					src="img/race_azterk.gif" border="0"></font></td>
				<td width="50" class="hc"><font color="#AAAA77"
					face="verdana,arial" size="1"><img
					src="img/race_xillor.gif" border="0"></font></td>
				<td></td>
			</tr>
			<tr>
				<td><img src="img/cruiser.gif"> Cruisers</td>
				<td class="hc" id="humanCruisers"><?php echo fleetTableFormat($fleet->humanCruisers)?></td>
				<td class="hc" id="azterkCruisers"><?php echo fleetTableFormat($fleet->azterkCruisers)?></td>
				<td class="hc" id="xillorCruisers"><?php echo fleetTableFormat($fleet->xillorCruisers)?></td>
				<td></td>
			</tr>
			<tr>
				<td bgcolor="#222233"><img src="img/destroyer.gif"> Destroyers</td>
				<td bgcolor="#222233" class="hc" id="humanDestroyers"><?php echo fleetTableFormat($fleet->humanDestroyers)?></td>
				<td bgcolor="#222233" class="hc" id="azterkDestroyers"><?php echo fleetTableFormat($fleet->azterkDestroyers)?></td>
				<td bgcolor="#222233" class="hc" id="xillorDestroyers"><?php echo fleetTableFormat($fleet->xillorDestroyers)?></td>
				<td></td>
			</tr>
			<tr>
				<td><img src="img/scout.gif"> Scouts</td>
				<td class="hc" id="humanScouts"><?php echo fleetTableFormat($fleet->humanScouts)?></td>
				<td class="hc" id="azterkScouts"><?php echo fleetTableFormat($fleet->azterkScouts)?></td>
				<td class="hc" id="xillorScouts"><?php echo fleetTableFormat($fleet->xillorScouts)?></td>
				<td></td>
			</tr>
			<tr>
				<td bgcolor="#222233"><img src="img/bomber.gif"> Bombers</td>
				<td bgcolor="#222233" class="hc" id="humanBombers"><?php echo fleetTableFormat($fleet->humanBombers)?></td>
				<td bgcolor="#222233" class="hc" id="azterkBombers"><?php echo fleetTableFormat($fleet->azterkBombers)?></td>
				<td bgcolor="#222233" class="hc" id="xillorBombers"><?php echo fleetTableFormat($fleet->xillorBombers)?></td>
				<td></td>
			</tr>
			<tr>
				<td><img src="img/army.gif"> Armies</td>
				<td class="hc" id="humanArmies"><?php echo fleetTableFormat($fleet->humanArmies)?></td>
				<td class="hc" id="azterkArmies"><?php echo fleetTableFormat($fleet->azterkArmies)?></td>
				<td class="hc" id="xillorArmies"><?php echo fleetTableFormat($fleet->xillorArmies)?></td>
				<td></td>
			</tr>
		</tbody>
	</table>
	<?php
	return ob_get_clean();
}

/**
 * Formats a number for the fleet table.
 * @param integer $num the number
 * @return string the formatted number 
 */
function fleetTableFormat($num){
	if ($num == 0){
		return '-';
	}
	return number_format($num);
}