<?php
namespace HAPI;

class FleetsInfo{
	private $ownPlanet;
	private $planetName;
	private $stasis;
	private $vacation;
	private $nrj;
	private $nrjMax;
	
	/**
	 * @var array(Fleet)
	 */
	private $fleets = array();
	
	public function isOwnPlanet(){
		return $this->ownPlanet;
	}

	public function setOwnPlanet($ownPlanet){
		$this->ownPlanet = $ownPlanet;
	}

	public function getPlanetName(){
		return $this->planetName;
	}

	public function setPlanetName($planetName){
		$this->planetName = $planetName;
	}

	public function isStasis(){
		return $this->stasis;
	}

	public function setStasis($stasis){
		$this->stasis = $stasis;
	}

	public function isVacation(){
		return $this->vacation;
	}

	public function setVacation($vacation){
		$this->vacation = $vacation;
	}

	public function getNrj(){
		return $this->nrj;
	}

	public function setNrj($nrj){
		$this->nrj = $nrj;
	}

	public function getNrjMax(){
		return $this->nrjMax;
	}

	public function setNrjMax($nrjMax){
		$this->nrjMax = $nrjMax;
	}

	public function getFleets(){
		return $this->fleets;
	}

	public function setFleets($fleets){
		$this->fleets = $fleets;
	}
}