<?php
namespace HAPI;

class Trade{
	private $id;
	private $planetName;
	private $planetTag;
	private $planetDistance;
	private $planetX;
	private $planetY;
	private $planetRace;
	private $planetActivity;
	private $income;
	private $capacity;
	private $transportType;
	private $pending;
	private $accepted;
	private $requestor;
	private $upkeep;
	private $prodType;
	private $planetBlockaded;
	
	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
	}

	public function getPlanetName(){
		return $this->planetName;
	}

	public function setPlanetName($planetName){
		$this->planetName = $planetName;
	}

	public function getPlanetTag(){
		return $this->planetTag;
	}

	public function setPlanetTag($planetTag){
		$this->planetTag = $planetTag;
	}

	public function getPlanetDistance(){
		return $this->planetDistance;
	}

	public function setPlanetDistance($planetDistance){
		$this->planetDistance = $planetDistance;
	}

	public function getPlanetX(){
		return $this->planetX;
	}

	public function setPlanetX($planetX){
		$this->planetX = $planetX;
	}

	public function getPlanetY(){
		return $this->planetY;
	}

	public function setPlanetY($planetY){
		$this->planetY = $planetY;
	}

	public function getPlanetRace(){
		return $this->planetRace;
	}

	public function setPlanetRace($planetRace){
		$this->planetRace = $planetRace;
	}

	public function getPlanetActivity(){
		return $this->planetActivity;
	}

	public function setPlanetActivity($planetActivity){
		$this->planetActivity = $planetActivity;
	}

	public function getIncome(){
		return $this->income;
	}

	public function setIncome($income){
		$this->income = $income;
	}

	public function getCapacity(){
		return $this->capacity;
	}

	public function setCapacity($capacity){
		$this->capacity = $capacity;
	}

	public function getTransportType(){
		return $this->transportType;
	}

	public function setTransportType($transportType){
		$this->transportType = $transportType;
	}

	public function isPending(){
		return $this->pending;
	}

	public function setPending($pending){
		$this->pending = $pending;
	}

	public function isAccepted(){
		return $this->accepted;
	}

	public function setAccepted($accepted){
		$this->accepted = $accepted;
	}

	public function isRequestor(){
		return $this->requestor;
	}

	public function setRequestor($requestor){
		$this->requestor = $requestor;
	}

	public function getUpkeep(){
		return $this->upkeep;
	}

	public function setUpkeep($upkeep){
		$this->upkeep = $upkeep;
	}

	public function getProdType(){
		return $this->prodType;
	}

	public function setProdType($prodType){
		$this->prodType = $prodType;
	}

	public function isPlanetBlockaded(){
		return $this->planetBlockaded;
	}

	public function setPlanetBlockaded($planetBlockaded){
		$this->planetBlockaded = $planetBlockaded;
	}
}