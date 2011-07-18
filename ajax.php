<?php
/**
 * Use this script to handle all AJAX requests. 
 */

require_once 'lib/bootstrap.php';
use HAPI\HAPI;
use ajax\Report;

//has the player logged in?
if (!Session::isLoggedIn()){
	header('', true, 400);
	exit();
}

$hapi = Session::getHapi();
$player = Session::getPlayer();
$mock = Session::isMockEnabled();
//$dao = $mock ? new HypToolsMockDao($player->game) : new HypToolsMySqlDao($player->game);

$method = @$_REQUEST['method'];
if ($method == 'report'){
	header('Content-Type: application/json');
	
	//query Hyperiums
	$hapiFleetsInfo = $hapi->getFleetsInfo();
	
	//save to the session so the exact same data can be used when the user submits the report
	Session::setHapiFleetsInfo($hapiFleetsInfo);
	
	//generate ajax response
	$report = new Report();
	foreach ($hapiFleetsInfo as $hapiFleetInfo){
		$hapiFleets = $hapiFleetInfo->getFleets();
		foreach ($hapiFleets as $hapiFleet){
			$race = $hapiFleet->getRace();
			if ($race == HAPI::RACE_AZTERK){
				$report->azterkScouts += $hapiFleet->getScouts();
				$report->azterkBombers += $hapiFleet->getBombers();
				$report->azterkDestroyers += $hapiFleet->getDestroyers();
				$report->azterkCruisers += $hapiFleet->getCruisers();
				$report->azterkArmies += $hapiFleet->getGroundArmies();
				$report->azterkArmies += $hapiFleet->getCarriedArmies();
			} else if ($race == HAPI::RACE_HUMAN){
				$report->humanScouts += $hapiFleet->getScouts();
				$report->humanBombers += $hapiFleet->getBombers();
				$report->humanDestroyers += $hapiFleet->getDestroyers();
				$report->humanCruisers += $hapiFleet->getCruisers();
				$report->humanArmies += $hapiFleet->getGroundArmies();
				$report->humanArmies += $hapiFleet->getCarriedArmies();
			} else if ($race == HAPI::RACE_XILLOR){
				$report->xillorScouts += $hapiFleet->getScouts();
				$report->xillorBombers += $hapiFleet->getBombers();
				$report->xillorDestroyers += $hapiFleet->getDestroyers();
				$report->xillorCruisers += $hapiFleet->getCruisers();
				$report->xillorArmies += $hapiFleet->getGroundArmies();
				$report->xillorArmies += $hapiFleet->getCarriedArmies();
			}
		}
	}
	
	$report->avgSpaceP += $report->azterkScouts * AvgP::AZTERK_SCOUT;
	$report->avgSpaceP += $report->azterkBombers * AvgP::AZTERK_BOMBER;
	$report->avgSpaceP += $report->azterkDestroyers * AvgP::AZTERK_DESTROYER;
	$report->avgSpaceP += $report->azterkCruisers * AvgP::AZTERK_CRUISER;
	$report->avgGroundP += $report->azterkArmies * AvgP::AZTERK_ARMY;
	
	$report->avgSpaceP += $report->humanScouts * AvgP::HUMAN_SCOUT;
	$report->avgSpaceP += $report->humanBombers * AvgP::HUMAN_BOMBER;
	$report->avgSpaceP += $report->humanDestroyers * AvgP::HUMAN_DESTROYER;
	$report->avgSpaceP += $report->humanCruisers * AvgP::HUMAN_CRUISER;
	$report->avgGroundP += $report->humanArmies * AvgP::HUMAN_ARMY;
	
	$report->avgSpaceP += $report->xillorScouts * AvgP::XILLOR_SCOUT;
	$report->avgSpaceP += $report->xillorBombers * AvgP::XILLOR_BOMBER;
	$report->avgSpaceP += $report->xillorDestroyers * AvgP::XILLOR_DESTROYER;
	$report->avgSpaceP += $report->xillorCruisers * AvgP::XILLOR_CRUISER;
	$report->avgGroundP += $report->xillorArmies * AvgP::XILLOR_ARMY;
	
	echo json_encode($report);
} else {
	header('', true, 400);
}
