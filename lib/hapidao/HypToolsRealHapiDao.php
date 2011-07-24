<?php
namespace hapidao;

use \Cache;
use HAPI\HAPI;

/**
 * Makes calls to the HAPI webservice.
 * @author mangstadt
 */
class HypToolsRealHapiDao implements HypToolsHapiDao{
	private $hapi;
	
	public function __construct(HAPI $hapi = null){
		$this->hapi = $hapi;
	}
	
	public function setPlayerIdentifier($hapi){
		$this->hapi = $hapi;
	}
	
	/**
	 * Gets all games.
	 * @return array(HAPI\Game) the games
	 */
	public function getGames(){
		return Cache::getGamesList(function(){
			return HAPI::getAllGames();
		});
	}
	
	/**
	 * Gets fleets info.
	 * @return array(HAPI\FleetsInfo) the fleets info
	 */
	public function getFleetsInfo(){
		return $this->hapi->getFleetsInfo();
	}
}