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

$player = Session::getPlayer();
$mock = Session::isMockEnabled();
$dao = $mock ? new HypToolsMockDao($player->game) : new HypToolsMySqlDao($player->game);

//user has requested to cancel a pending join request
$cancelJoinRequest = @$_POST['cancelJoinRequest'];
if ($cancelJoinRequest !== null){
	$joinRequest = $dao->selectJoinRequestById($cancelJoinRequest);
	if ($joinRequest != null && $joinRequest->player->id == $player->id){
		$dao->beginTransaction();
		try{
			$dao->deleteJoinRequest($joinRequest->id);
			$dao->insertJoinLog($player, $joinRequest->alliance, JoinLog::EVENT_CANCELLED);
			$dao->commit();
		} catch (Exception $e){
			$dao->rollBack();
			throw $e;
		}
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
						$dao->beginTransaction();
						try{
							$dao->insertPresidentPermission($player, $alliance);
							$dao->insertJoinLog($player, $alliance, JoinLog::EVENT_ACCEPTED);
							$dao->commit();
						} catch (Exception $e){
							$dao->rollBack();
							throw $e;
						}
						$joinTagSuccess = "Hello Mr. President.";
					} else {
						$dao->beginTransaction();
						try{
							$dao->insertJoinRequest($player, $alliance);
							$dao->insertJoinLog($player, $alliance, JoinLog::EVENT_REQUESTED);
							$dao->commit();
						} catch (Exception $e){
							$dao->rollBack();
							throw $e;
						}
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

//get notifications
$joinLogs = $dao->selectJoinLogsByPlayer($player);

//get the player's pending join requests
$playerJoinRequests = $dao->selectJoinRequestsByPlayer($player);

//get the alliances the player belongs to
$playerAlliances = $dao->selectPermissionsByPlayer($player);

//get the time the player last submitted a report
$submitLog = $dao->selectLastPlayerSubmitLog($player);

?>
<!DOCTYPE html>
<html lang="en">

	<head>

		<meta charset="utf-8" />
		<title>Hyperiums Alliance Tools</title>

		<link rel="stylesheet" href="css/hat.less" type="text/less" />
		
		<link  href="http://fonts.googleapis.com/css?family=Metrophobic:regular" rel="stylesheet" type="text/css" />

	</head>
	
	<script src="js/global.js"></script>
	
	<script type="text/javascript">
		/**
		 * Adds commas to a number or returns "-" if the number is 0.
		 * @param num the number
		 * @return the number with commas or "-" if the number is 0
		 */
		function formatNum(num){
			if (num == 0){
				return "-";
			}
			return addCommas(num);
		}

		/**
		 * Generates a HTML table for displaying the avgP bars.
		 * @param avgP the avgP
		 * @return the HTML table
		 */
		function generateAvgPBars(avgP){
			var html = '<table cellspacing="1" cellpadding="1" border="0"><tr>';
			
			var blocks = [
				[500000, 'pow22'],
				[50000, 'pow21'],
				[5000, 'pow20']
			];
			var mod = avgP;
			for (var i = 0; i < blocks.length; i++){
				var block = blocks[i];
				var count = Math.floor(mod / block[0]);
				for (var j = 0; j < count; j++){
					html += '<td class="pow ' + block[1] + '"></td>';
				}
				mod = mod % block[0];
			}

			html += '</tr></table>';
			return html;
		}

		/**
		 * Formats the avgP for display.
		 * @param avgP the avgP
		 * @return the formatted avgP (example: "465.2K")
		 */
		function generateAvgPText(avgP){
			if (avgP > 1000000) {
				avgP = addCommas((avgP / 1000000).toFixed(1)) + "M";
			} else if (avgP > 1000) {
				avgP = addCommas((avgP / 1000).toFixed(1)) + "K";
			}
			return avgP;
		}
	
		function prepareReport(){
			$("prepareDiv").style.display = "none";
			$("loading").style.display = "block";
			$("loadingMessage").innerHTML = "Please wait while your report is generated.<br />This may take some time...";
	
			var xmlhttp = newXmlhttp();
			xmlhttp.onreadystatechange = function(){
				if (xmlhttp.readyState == 4){
					$("loading").style.display = "none";
					$("error").style.display = "none";
					
					if (xmlhttp.status == 200){
						//parse response
						var report = eval('(' + xmlhttp.responseText + ')');

						//populate table with individual unit counts
						$("azterkScouts").innerHTML = formatNum(report.azterkScouts);
						$("azterkBombers").innerHTML = formatNum(report.azterkBombers);
						$("azterkDestroyers").innerHTML = formatNum(report.azterkDestroyers);
						$("azterkCruisers").innerHTML = formatNum(report.azterkCruisers);
						$("azterkArmies").innerHTML = formatNum(report.azterkArmies);
						$("humanScouts").innerHTML = formatNum(report.humanScouts);
						$("humanBombers").innerHTML = formatNum(report.humanBombers);
						$("humanDestroyers").innerHTML = formatNum(report.humanDestroyers);
						$("humanCruisers").innerHTML = formatNum(report.humanCruisers);
						$("humanArmies").innerHTML = formatNum(report.humanArmies);
						$("xillorScouts").innerHTML = formatNum(report.xillorScouts);
						$("xillorBombers").innerHTML = formatNum(report.xillorBombers);
						$("xillorDestroyers").innerHTML = formatNum(report.xillorDestroyers);
						$("xillorCruisers").innerHTML = formatNum(report.xillorCruisers);
						$("xillorArmies").innerHTML = formatNum(report.xillorArmies);

						//generate "avgP" bars
						$("spaceAvgPBars").innerHTML = generateAvgPBars(report.avgSpaceP);
						$("spaceAvgP").innerHTML = generateAvgPText(report.avgSpaceP);
						$("groundAvgPBars").innerHTML = generateAvgPBars(report.avgGroundP);
						$("groundAvgP").innerHTML = generateAvgPText(report.avgGroundP);

						$("factories").innerHTML = addCommas(report.factories);
	
						$("report").style.display = "block";
						$("submitDiv").style.display = "block";
						
					} else {
						//error
						$("error").style.display = "block";
						$("error").innerHTML = "Error generating report: HTTP " + xmlhttp.status + " " + xmlhttp.statusText + "<br />" + xmlhttp.responseText;
						$("prepareDiv").style.display = "block";
					}
				}
			};
			
			xmlhttp.open("GET","ajax.php?method=report", true);
			xmlhttp.send();
		}

		function submitReport(){
			$("submitDiv").style.display = "none";
			$("loading").style.display = "block";
			$("loadingMessage").innerHTML = "Submitting report...";

			var xmlhttp = newXmlhttp();
			xmlhttp.onreadystatechange = function(){
				if (xmlhttp.readyState == 4){
					$("loading").style.display = "none";
					$("error").style.display = "none";
					
					if (xmlhttp.status == 200){
						$("message").innerHTML = '<img src="img/checkmark.png" />Submission complete';
						$("message").style.display = "block";
					} else {
						//error
						$("error").style.display = "block";
						$("error").innerHTML = "Error submitting report: HTTP " + xmlhttp.status + " " + xmlhttp.statusText + "<br />" + xmlhttp.responseText;
						$("submitDiv").style.display = "block";
					}
				}
			};
			
			xmlhttp.open("POST","ajax.php?method=submit", true);
			xmlhttp.send();
		}
	</script>


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
				
				<div class="block">
					<h1>Notifications</h1>
					<div>
						<?php
						if (count($joinLogs) == 0):
							?><i>None</i><?php
						else:
							?>
							<table style="width:100%">
							<?php
							foreach ($joinLogs as $joinLog):
								//$new = $joinLog->eventDate->format("U") - $player->lastLoginDate->format("U") > 0;
								$new = false;
								$description = null;
								switch($joinLog->event):
									case JoinLog::EVENT_REQUESTED:
										$description = "You have sent an authentication request to the <b>[" . htmlspecialchars($joinLog->alliance->tag) . "]</b> alliance.";
										 break;
									case JoinLog::EVENT_CANCELLED:
										$description = "You have cancelled your authentication request to the <b>[" . htmlspecialchars($joinLog->alliance->tag) . "]</b> alliance.";
										break;
									case JoinLog::EVENT_ACCEPTED:
										$description = "Your authentication request to the <b>[" . htmlspecialchars($joinLog->alliance->tag) . "]</b> alliance has been approved.";
										break;
									case JoinLog::EVENT_REJECTED:
										$description = "Your authentivation request to the <b>[" . htmlspecialchars($joinLog->alliance->tag) . "]</b> alliance has been rejected.";
										break;
									case JoinLog::EVENT_REMOVED:
										$description = "You have been removed from the <b>[" . htmlspecialchars($joinLog->alliance->tag) . "]</b> alliance.";
										break;
								endswitch;

								if ($description != null):
									?>
									<tr>
										<td valign="top">
											<?php echo $new ? "<b>" : ""?>	
											<?php echo htmlspecialchars($joinLog->eventDate->format("Y-m-d G:i T"))?>
											<?php echo $new ? "</b>" : ""?>
										</td>
										<td valign="top">
											<?php echo $new ? "<b>" : ""?>
											<?php echo $description?>
											<?php echo $new ? "</b>" : ""?>
										</td>
									</tr>
									<?php
								endif;
							endforeach;
							?>
							</table>
							<?php
						endif;
						?>
					</div>
				</div>
				
				<div class="block">
					<h1>Submit Report</h1>
					<div>
						<p>Upload your fleet, trading, and infiltration data to your alliances.
						You will be able to view your report before it is submitted.</p>
						
						<?php
						if (count($playerAlliances) == 0):
							?>
							<div align="center" id="prepareDiv">
								<i>Not a member of an alliance.</i><br />
								<button class="button" id="prepareButton" disabled="disabled">Prepare Report</button>
							</div>
							<?php
						else: 
							?>
							<div id="report" style="display:none">
								<b>Fleet Report</b>
								
								<table cellspacing="10">
									<tr>
										<td>
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
														<td class="hc" id="humanCruisers"></td>
														<td class="hc" id="azterkCruisers"></td>
														<td class="hc" id="xillorCruisers"></td>
														<td></td>
													</tr>
													<tr>
														<td bgcolor="#222233"><img src="img/destroyer.gif"> Destroyers</td>
														<td bgcolor="#222233" class="hc" id="humanDestroyers"></td>
														<td bgcolor="#222233" class="hc" id="azterkDestroyers"></td>
														<td bgcolor="#222233" class="hc" id="xillorDestroyers"></td>
														<td></td>
													</tr>
													<tr>
														<td><img src="img/scout.gif"> Scouts</td>
														<td class="hc" id="humanScouts"></td>
														<td class="hc" id="azterkScouts"></td>
														<td class="hc" id="xillorScouts"></td>
														<td></td>
													</tr>
													<tr>
														<td bgcolor="#222233"><img src="img/bomber.gif"> Bombers</td>
														<td bgcolor="#222233" class="hc" id="humanBombers"></td>
														<td bgcolor="#222233" class="hc" id="azterkBombers"></td>
														<td bgcolor="#222233" class="hc" id="xillorBombers"></td>
														<td></td>
													</tr>
													<tr>
														<td><img src="img/army.gif"> Armies</td>
														<td class="hc" id="humanArmies"></td>
														<td class="hc" id="azterkArmies"></td>
														<td class="hc" id="xillorArmies"></td>
														<td></td>
													</tr>
												</tbody>
											</table>
										</td>
										<td>
											<table cellspacing="1" cellpadding="1" border="0">
												<tr>
													<td>Space AvgP:</td>
													<td id="spaceAvgPBars"></td>
													<td id="spaceAvgP"></td>
												</tr>
												<tr>
													<td>Ground AvgP:</td>
													<td id="groundAvgPBars"></td>
													<td id="groundAvgP"></td>
												</tr>
												<tr>
													<td><img src="img/factory.gif" /> Factories:</td>
													<td colspan="2" id="factories"></td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
								<br /><br />
								
								<b>Trade Report</b><br />
								<i>Coming soon...</i><br />
								<br />
								
								<b>Infiltration Report</b><br />
								<i>Coming soon...</i><br />
							</div>
							
							<div align="center" id="message"></div>
							
							<div id="error" style="color:red; display:none"></div>
							
							<div align="center" id="loading" style="display:none">
								<img src="img/loading.gif" />
								<div id="loadingMessage"></div>
							</div>
							
							<div align="center" id="prepareDiv">
								<?php
								if ($submitLog != null):
									?>
									Last submission: <?php echo htmlspecialchars($submitLog->submitDate->format('Y-m-d G:i T'))?><br />
									<?php 
								endif;
								?>
								<button class="button" id="prepareButton" onclick="prepareReport()">Prepare Report</button>
							</div>
							
							<div align="center" style="display:none" id="submitDiv">
								<button class="button" id="submitButton" onclick="submitReport()">Submit Report</button>
							</div>
						</div>
						<?php 
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