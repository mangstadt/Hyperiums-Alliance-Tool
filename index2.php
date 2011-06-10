<?php

	error_reporting(E_ERROR) ;

	require_once dirname(__FILE__) .  '/lib/HAPI/bootstrap.php';

	use HAPI\HAPI;
	use HAPI\Game;

	//static methods in HAPI class don't require authentication
	foreach (HAPI::getAllGames() as $game){
		$name = $game->getName();
		$description = $game->getDescription();
		$state = $game->getState();
		switch ($state){
			case Game::STATE_NOT_RUNNING_CLOSED:
				$state = "not running, closed";
				break;
			case Game::STATE_NOT_RUNNING_OPEN_REGISTRATION:
				$state = "not running, open for registration";
				break;
			case Game::STATE_RUNNING_CLOSED:
				$state = "running, closed to new players";
				break;
			case Game::STATE_RUNNING_OPEN:
				$state = "running, open to new players";
				break;
		}
		$initCash = number_format($game->getInitCash());
		echo "$name: $description -- $state<br/>";
	}

	//authenticate with HAPI by creating a new object
	$hapi = null;
	try{
		$hapi = new HAPI("Hyperiums6", "JeromeM62", "f04b63d5480e19d150");
	} catch (Exception $e){
		//an exception is thrown if there is an authentication failure
		echo "Error authenticating: " . $e->getMessage();
	}

	//you can save the HAPI object to the PHP session and re-use it later
	//this makes things faster, because when you construct a new HAPI object, it sends an auth request, which you don't have to do if you've already authenticated
	//$_SESSION['hapi'] = $hapi;

	try{
		//then, simply call the methods from the HAPI class
		$movingFleets = $hapi->getMovingFleets();
		foreach ($movingFleets as $mf){
			$name = $mf->getName();
			if ($name == null){
				$name = "no name";
			}
			$from = $mf->getFrom();
			$to = $mf->getTo();
			$action = $mf->isDefending() ? "defend" : "attack";
			$eta = $mf->getDistance();

			echo "Fleet \"$name\" is moving from $from to $to and will $action it. ETA $eta hours.<br/>";
		}
	} catch (Exception $e){
		echo "Error getting moving fleets: " . $e->getMessage();
	}
	
	try {
		$playerInfo = $hapi->getPlayerInfo() ;
		$cash = $playerInfo->getCash() ;
		echo $cash ;
	} catch (Exception $e) {
		echo "Error .." ;
	}
	
?>