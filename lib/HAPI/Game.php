<?php
namespace HAPI;

class Game{
	const STATE_NOT_RUNNING_CLOSED = -1;
	const STATE_RUNNING_CLOSED = 0;
	const STATE_RUNNING_OPEN = 1;
	const STATE_NOT_RUNNING_OPEN_REGISTRATION = 2;

	private $name;
	private $state;
	private $description;
	private $length;
	private $maxEndDate;
	private $peec;
	private $maxPlanets;
	private $initCash;
	private $maxOfferedPlanets;
	private $nextPlanetDelay;
	
	public function getName(){
		return $this->name;
	}

	public function setName($name){
		$this->name = $name;
	}

	public function getState(){
		return $this->state;
	}

	public function setState($state){
		$this->state = $state;
	}

	public function getDescription(){
		return $this->description;
	}

	public function setDescription($description){
		$this->description = $description;
	}

	public function getLength(){
		return $this->length;
	}

	public function setLength($length){
		$this->length = $length;
	}

	public function getMaxEndDate(){
		return $this->maxEndDate;
	}

	public function setMaxEndDate($maxEndDate){
		$this->maxEndDate = $maxEndDate;
	}

	public function isPeec(){
		return $this->peec;
	}

	public function setPeec($peec){
		$this->peec = $peec;
	}

	public function getMaxPlanets(){
		return $this->maxPlanets;
	}

	public function setMaxPlanets($maxPlanets){
		$this->maxPlanets = $maxPlanets;
	}

	public function getInitCash(){
		return $this->initCash;
	}

	public function setInitCash($initCash){
		$this->initCash = $initCash;
	}

	public function getMaxOfferedPlanets(){
		return $this->maxOfferedPlanets;
	}

	public function setMaxOfferedPlanets($maxOfferedPlanets){
		$this->maxOfferedPlanets = $maxOfferedPlanets;
	}

	public function getNextPlanetDelay(){
		return $this->nextPlanetDelay;
	}

	public function setNextPlanetDelay($nextPlanetDelay){
		$this->nextPlanetDelay = $nextPlanetDelay;
	}
}