<?php
require_once 'lib/bootstrap.php';
use db\Report;
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

//get the reports of all alliance members
$reports = $dao->selectReportsByAlliance($alliance);

//sum up all reports
$reportTotals = new Report();
foreach ($reports as $report){
	$reportTotals->azterkScouts += $report->azterkScouts;
	$reportTotals->azterkDestroyers += $report->azterkDestroyers;
	$reportTotals->azterkBombers += $report->azterkBombers;
	$reportTotals->azterkCruisers += $report->azterkCruisers;
	$reportTotals->azterkArmies += $report->azterkArmies;
	
	$reportTotals->humanScouts += $report->humanScouts;
	$reportTotals->humanDestroyers += $report->humanDestroyers;
	$reportTotals->humanBombers += $report->humanBombers;
	$reportTotals->humanCruisers += $report->humanCruisers;
	$reportTotals->humanArmies += $report->humanArmies;
	
	$reportTotals->xillorScouts += $report->xillorScouts;
	$reportTotals->xillorDestroyers += $report->xillorDestroyers;
	$reportTotals->xillorBombers += $report->xillorBombers;
	$reportTotals->xillorCruisers += $report->xillorCruisers;
	$reportTotals->xillorArmies += $report->xillorArmies;
	
	$reportTotals->factories += $report->factories;
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
					if (count($reports) == 0):
						?><i>No reports submitted.</i><?php
					else:
						?>
						<h2>[<?php echo htmlspecialchars($alliance->tag)?>] Total Fleet Power</h2>
						<div align="center" style="border-bottom:5px solid #fff; padding-bottom:15px;">
							<table cellspacing="10">
								<tr>
									<td><?php echo fleetTable($reportTotals)?></td>
									<td><?php echo avgPTable($reportTotals)?></td>
								</tr>
							</table>
						</div>
						
						<h2>Fleets by Player</h2>
						<table width="100%" cellpadding="10">
							<?php
							for ($i = 0; $i < count($reports); $i++):
								$report = $reports[$i];
								
								if ($i % 2 == 0):
									?><tr><?php
								endif;
								
								?>
								<td>
									<b><?php echo htmlspecialchars($report->player->name)?></b><br />
									Last Submission: <?php echo htmlspecialchars($report->submitDate->format("Y-m-d G:i T"))?>
									<?php echo fleetTable($report)?>
									<?php echo avgPTable($report)?>
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
 * @param Report $report the report
 * @return string the HTML table
 */
function avgPTable(Report $report){
	$spaceAvgP = 0;
	$groundAvgP = 0;
	
	$spaceAvgP += $report->azterkScouts * AvgP::AZTERK_SCOUT;
	$spaceAvgP += $report->azterkDestroyers * AvgP::AZTERK_DESTROYER;
	$spaceAvgP += $report->azterkBombers * AvgP::AZTERK_BOMBER;
	$spaceAvgP += $report->azterkCruisers * AvgP::AZTERK_CRUISER;
	$groundAvgP += $report->azterkArmies * AvgP::AZTERK_ARMY;

	$spaceAvgP += $report->humanScouts * AvgP::HUMAN_SCOUT;
	$spaceAvgP += $report->humanDestroyers* AvgP::HUMAN_DESTROYER;
	$spaceAvgP += $report->humanBombers * AvgP::HUMAN_BOMBER;
	$spaceAvgP += $report->humanCruisers * AvgP::HUMAN_CRUISER;
	$groundAvgP += $report->humanArmies * AvgP::HUMAN_ARMY;

	$spaceAvgP += $report->xillorScouts * AvgP::XILLOR_SCOUT;
	$spaceAvgP += $report->xillorDestroyers * AvgP::XILLOR_DESTROYER;
	$spaceAvgP += $report->xillorBombers * AvgP::XILLOR_BOMBER;
	$spaceAvgP += $report->xillorCruisers * AvgP::XILLOR_CRUISER;
	$groundAvgP += $report->xillorArmies * AvgP::XILLOR_ARMY;
	
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
	
	$factoriesText = number_format($report->factories);
	
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
		<tr>
			<td><img src="img/factory.gif" /> Factories:</td>
			<td colspan="2"><?php echo $factoriesText?></td>
		</tr>
	</table>
	<?php
	return ob_get_clean();
}

/**
 * Builds the table that shows unit by unit breakdown of a fleet.
 * @param Report $report the report
 * @return string the HTML table
 */
function fleetTable(Report $report){
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
				<td class="hc" id="humanCruisers"><?php echo fleetTableFormat($report->humanCruisers)?></td>
				<td class="hc" id="azterkCruisers"><?php echo fleetTableFormat($report->azterkCruisers)?></td>
				<td class="hc" id="xillorCruisers"><?php echo fleetTableFormat($report->xillorCruisers)?></td>
				<td></td>
			</tr>
			<tr>
				<td bgcolor="#222233"><img src="img/destroyer.gif"> Destroyers</td>
				<td bgcolor="#222233" class="hc" id="humanDestroyers"><?php echo fleetTableFormat($report->humanDestroyers)?></td>
				<td bgcolor="#222233" class="hc" id="azterkDestroyers"><?php echo fleetTableFormat($report->azterkDestroyers)?></td>
				<td bgcolor="#222233" class="hc" id="xillorDestroyers"><?php echo fleetTableFormat($report->xillorDestroyers)?></td>
				<td></td>
			</tr>
			<tr>
				<td><img src="img/scout.gif"> Scouts</td>
				<td class="hc" id="humanScouts"><?php echo fleetTableFormat($report->humanScouts)?></td>
				<td class="hc" id="azterkScouts"><?php echo fleetTableFormat($report->azterkScouts)?></td>
				<td class="hc" id="xillorScouts"><?php echo fleetTableFormat($report->xillorScouts)?></td>
				<td></td>
			</tr>
			<tr>
				<td bgcolor="#222233"><img src="img/bomber.gif"> Bombers</td>
				<td bgcolor="#222233" class="hc" id="humanBombers"><?php echo fleetTableFormat($report->humanBombers)?></td>
				<td bgcolor="#222233" class="hc" id="azterkBombers"><?php echo fleetTableFormat($report->azterkBombers)?></td>
				<td bgcolor="#222233" class="hc" id="xillorBombers"><?php echo fleetTableFormat($report->xillorBombers)?></td>
				<td></td>
			</tr>
			<tr>
				<td><img src="img/army.gif"> Armies</td>
				<td class="hc" id="humanArmies"><?php echo fleetTableFormat($report->humanArmies)?></td>
				<td class="hc" id="azterkArmies"><?php echo fleetTableFormat($report->azterkArmies)?></td>
				<td class="hc" id="xillorArmies"><?php echo fleetTableFormat($report->xillorArmies)?></td>
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