<?php
/**
 * Use this script to handle all AJAX requests. 
 */

require_once 'lib/bootstrap.php';
use ajax\Report;
use db\HypToolsMockDao;
use db\HypToolsMySqlDao;
use db\Fleet;
use HAPI\HAPI;
use hapidao\HypToolsRealHapiDao;
use hapidao\HypToolsMockHapiDao;

//has the player logged in?
if (!Session::isLoggedIn()){
	header('', true, 400);
	echo 'Not logged in.';
	exit();
}

$player = Session::getPlayer();
$mock = Session::isMockEnabled();
$dao = $mock ? new HypToolsMockDao($player->game) : new HypToolsMySqlDao($player->game);
$hapiDao = $mock ? new HypToolsMockHapiDao($player->name) : new HypToolsRealHapiDao(Session::getHapi());

$method = @$_REQUEST['method'];
if ($method == 'report'){
	header('Content-Type: application/json');
	
	//query Hyperiums
	$hapiFleetsInfo = $hapiDao->getFleetsInfo();
	
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
	
	//send response
	echo json_encode($report);
} else if ($method == 'submit'){
	//get fleet info that was retrieved when the report was generated
	$hapiFleetsInfo = Session::getHapiFleetsInfo();
	if ($hapiFleetsInfo == null){
		header('', true, 400);
		echo 'Report has not been generated.';
		exit();
	}
	
	//build Fleet object
	$fleet = new Fleet();
	foreach ($hapiFleetsInfo as $hapiFleetInfo){
		$hapiFleets = $hapiFleetInfo->getFleets();
		foreach ($hapiFleets as $hapiFleet){
			$fleet->player = $player;
			$race = $hapiFleet->getRace();
			if ($race == HAPI::RACE_AZTERK){
				$fleet->azterkScouts += $hapiFleet->getScouts();
				$fleet->azterkBombers += $hapiFleet->getBombers();
				$fleet->azterkDestroyers += $hapiFleet->getDestroyers();
				$fleet->azterkCruisers += $hapiFleet->getCruisers();
				$fleet->azterkArmies += $hapiFleet->getGroundArmies();
				$fleet->azterkArmies += $hapiFleet->getCarriedArmies();
			} else if ($race == HAPI::RACE_HUMAN){
				$fleet->humanScouts += $hapiFleet->getScouts();
				$fleet->humanBombers += $hapiFleet->getBombers();
				$fleet->humanDestroyers += $hapiFleet->getDestroyers();
				$fleet->humanCruisers += $hapiFleet->getCruisers();
				$fleet->humanArmies += $hapiFleet->getGroundArmies();
				$fleet->humanArmies += $hapiFleet->getCarriedArmies();
			} else if ($race == HAPI::RACE_XILLOR){
				$fleet->xillorScouts += $hapiFleet->getScouts();
				$fleet->xillorBombers += $hapiFleet->getBombers();
				$fleet->xillorDestroyers += $hapiFleet->getDestroyers();
				$fleet->xillorCruisers += $hapiFleet->getCruisers();
				$fleet->xillorArmies += $hapiFleet->getGroundArmies();
				$fleet->xillorArmies += $hapiFleet->getCarriedArmies();
			}
		}
	}
	
	//save to database
	$dao->beginTransaction();
	try{
		$dao->deleteFleetsByPlayer($player);
		$dao->insertFleet($fleet);
		$dao->insertSubmitLog($player);
		$dao->commit();
	} catch (Exception $e){
		$dao->rollBack();
		throw $e;
	}
	sleep(2);
} else {
	header('', true, 400);
	echo "Method named \"$method\" does not exist.";
}
