<?php
/**
 * Use this script to handle all AJAX requests. 
 */

require_once 'lib/bootstrap.php';
use db\HypToolsMockDao;
use db\HypToolsMySqlDao;
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
	//query Hyperiums
	$hapiFleetsInfo = $hapiDao->getFleetsInfo();
	$hapiPlanetInfos = $hapiDao->getPlanetInfo();

	//build Report object
	$report = new db\Report();
	$report->player = $player;
	foreach ($hapiFleetsInfo as $hapiFleetInfo){
		$hapiFleets = $hapiFleetInfo->getFleets();
		foreach ($hapiFleets as $hapiFleet){
			if (strcasecmp($hapiFleet->getOwner(), $player->name) == 0){ //only consider fleets that belong to the player
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
	}
	foreach ($hapiPlanetInfos as $hapiPlanetInfo){
		$report->factories += $hapiPlanetInfo->getNumFactories();
		$report->exploits += $hapiPlanetInfo->getNumExploits();
		foreach ($hapiPlanetInfo->getInfiltrations() as $hapiInfil){
			$infil = new ajax\Infiltration();
			$infil->planetName = $hapiInfil->getPlanetName();
			$infil->planetTag = $hapiInfil->getPlanetTag();
			$infil->x = $hapiInfil->getPlanetX();
			$infil->y = $hapiInfil->getPlanetY();
			$infil->level = $hapiInfil->getLevel();
			$infil->security = $hapiInfil->getSecurity();
			$infil->captive = $hapiInfil->isCaptive() == 1;
			$report->infiltrations[] = $infil;
		}
	}
	
	//save to the session so the exact same data can be used when the user submits the report
	Session::setReport($report);

	//generate ajax response
	$ajaxReport = new ajax\Report();
	$ajaxReport->azterkScouts = $report->azterkScouts;
	$ajaxReport->azterkBombers = $report->azterkBombers;
	$ajaxReport->azterkDestroyers = $report->azterkDestroyers;
	$ajaxReport->azterkCruisers = $report->azterkCruisers;
	$ajaxReport->azterkArmies = $report->azterkArmies;
	$ajaxReport->azterkArmies = $report->azterkArmies;
	$ajaxReport->humanScouts = $report->humanScouts;
	$ajaxReport->humanBombers = $report->humanBombers;
	$ajaxReport->humanDestroyers = $report->humanDestroyers;
	$ajaxReport->humanCruisers = $report->humanCruisers;
	$ajaxReport->humanArmies = $report->humanArmies;
	$ajaxReport->humanArmies = $report->humanArmies;
	$ajaxReport->xillorScouts = $report->xillorScouts;
	$ajaxReport->xillorBombers = $report->xillorBombers;
	$ajaxReport->xillorDestroyers = $report->xillorDestroyers;
	$ajaxReport->xillorCruisers = $report->xillorCruisers;
	$ajaxReport->xillorArmies = $report->xillorArmies;
	$ajaxReport->xillorArmies = $report->xillorArmies;
	
	$ajaxReport->avgSpaceP += $report->azterkScouts * AvgP::AZTERK_SCOUT;
	$ajaxReport->avgSpaceP += $report->azterkBombers * AvgP::AZTERK_BOMBER;
	$ajaxReport->avgSpaceP += $report->azterkDestroyers * AvgP::AZTERK_DESTROYER;
	$ajaxReport->avgSpaceP += $report->azterkCruisers * AvgP::AZTERK_CRUISER;
	$ajaxReport->avgGroundP += $report->azterkArmies * AvgP::AZTERK_ARMY;
	
	$ajaxReport->avgSpaceP += $report->humanScouts * AvgP::HUMAN_SCOUT;
	$ajaxReport->avgSpaceP += $report->humanBombers * AvgP::HUMAN_BOMBER;
	$ajaxReport->avgSpaceP += $report->humanDestroyers * AvgP::HUMAN_DESTROYER;
	$ajaxReport->avgSpaceP += $report->humanCruisers * AvgP::HUMAN_CRUISER;
	$ajaxReport->avgGroundP += $report->humanArmies * AvgP::HUMAN_ARMY;
	
	$ajaxReport->avgSpaceP += $report->xillorScouts * AvgP::XILLOR_SCOUT;
	$ajaxReport->avgSpaceP += $report->xillorBombers * AvgP::XILLOR_BOMBER;
	$ajaxReport->avgSpaceP += $report->xillorDestroyers * AvgP::XILLOR_DESTROYER;
	$ajaxReport->avgSpaceP += $report->xillorCruisers * AvgP::XILLOR_CRUISER;
	$ajaxReport->avgGroundP += $report->xillorArmies * AvgP::XILLOR_ARMY;
	
	$ajaxReport->factories = $report->factories;
	
	$ajaxReport->exploits = $report->exploits;
	
	$ajaxReport->infiltrations = $report->infiltrations;
	
	//send response
	header('Content-Type: application/json');
	echo json_encode($ajaxReport);
} else if ($method == 'submit'){
	//get report object that was generated when user viewed the report
	$report = Session::getReport();
	if ($report == null){
		header('', true, 400);
		echo 'Report has not been generated.';
		exit();
	}
	
	//save to database
	$dao->beginTransaction();
	try{
		$dao->deleteReportsByPlayer($player);
		$dao->insertReport($report);
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
