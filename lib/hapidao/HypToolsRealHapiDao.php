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
	
	//override
	public function setPlayerIdentifier($hapi){
		$this->hapi = $hapi;
	}
	
	//override
	public function getGames(){
		return Cache::getGamesList(function(){
			return HAPI::getAllGames();
		});
	}
	
	//override
	public function getFleetsInfo(){
		return $this->hapi->getFleetsInfo();
	}
	
	//override
	public function getPlanetInfo(){
		return $this->hapi->getPlanetInfo();
	}
}