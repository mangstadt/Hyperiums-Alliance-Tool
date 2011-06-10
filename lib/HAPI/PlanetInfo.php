<?php
namespace HAPI;

class PlanetInfo{
	private $name;
	private $x;
	private $y;
	private $size;
	private $orbit;
	private $government;
	private $prodType;
	private $tax;
	private $numExploits;
	private $numExploitsInPipe;
	private $activity;
	private $population;
	private $race;
	private $nrj;
	private $nrjMax;
	private $purifying;
	private $paranoidMode;
	private $blockaded;
	private $blackHole;
	private $stasis;
	private $nexus;
	private $nexusBuildTimeLeft;
	private $nexusBuildTimeTotal;
	private $ecomark;
	private $id;
	private $publicTag;
	private $numFactories;
	private $civLevel;
	private $defBonus;
	
	/**
	 * 
	 * @var array(Trade)
	 */
	private $trades = array();
	
	/**
	 * 
	 * @var array(Infiltration)
	 */
	private $infiltrations = array();
	
	public function getName(){
		return $this->name;
	}
	
	public function setName($name){
		$this->name = $name;
	}
	
	public function getX(){
		return $this->x;
	}

	public function setX($x){
		$this->x = $x;
	}

	public function getY(){
		return $this->y;
	}

	public function setY($y){
		$this->y = $y;
	}

	public function getSize(){
		return $this->size;
	}

	public function setSize($size){
		$this->size = $size;
	}

	public function getOrbit(){
		return $this->orbit;
	}

	public function setOrbit($orbit){
		$this->orbit = $orbit;
	}

	public function getGovernment(){
		return $this->government;
	}

	public function setGovernment($government){
		$this->government = $government;
	}

	public function getProdType(){
		return $this->prodType;
	}

	public function setProdType($prodType){
		$this->prodType = $prodType;
	}

	public function getTax(){
		return $this->tax;
	}

	public function setTax($tax){
		$this->tax = $tax;
	}

	public function getNumExploits(){
		return $this->numExploits;
	}

	public function setNumExploits($numExploits){
		$this->numExploits = $numExploits;
	}

	public function getNumExploitsInPipe(){
		return $this->numExploitsInPipe;
	}

	public function setNumExploitsInPipe($numExploitsInPipe){
		$this->numExploitsInPipe = $numExploitsInPipe;
	}

	public function getActivity(){
		return $this->activity;
	}

	public function setActivity($activity){
		$this->activity = $activity;
	}

	public function getPopulation(){
		return $this->population;
	}

	public function setPopulation($population){
		$this->population = $population;
	}

	public function getRace(){
		return $this->race;
	}

	public function setRace($race){
		$this->race = $race;
	}

	public function getNrj(){
		return $this->njr;
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

	public function isPurifying(){
		return $this->purifying;
	}

	public function setPurifying($purifying){
		$this->purifying = $purifying;
	}

	public function isParanoidMode(){
		return $this->paranoidMode;
	}

	public function setParanoidMode($paranoidMode){
		$this->paranoidMode = $paranoidMode;
	}

	public function isBlockaded(){
		return $this->blockaded;
	}

	public function setBlockaded($blockaded){
		$this->blockaded = $blockaded;
	}

	public function isBlackHole(){
		return $this->blackHole;
	}

	public function setBlackHole($blackHole){
		$this->blackHole = $blackHole;
	}

	public function isStasis(){
		return $this->stasis;
	}

	public function setStasis($stasis){
		$this->stasis = $stasis;
	}

	public function isNexus(){
		return $this->nexus;
	}

	public function setNexus($nexus){
		$this->nexus = $nexus;
	}

	public function getNexusBuildTimeLeft(){
		return $this->nexusBuildTimeLeft;
	}

	public function setNexusBuildTimeLeft($nexusBuildTimeLeft){
		$this->nexusBuildTimeLeft = $nexusBuildTimeLeft;
	}

	public function getNexusBuildTimeTotal(){
		return $this->nexusBuildTimeTotal;
	}

	public function setNexusBuildTimeTotal($nexusBuildTimeTotal){
		$this->nexusBuildTimeTotal = $nexusBuildTimeTotal;
	}

	public function getEcomark(){
		return $this->ecomark;
	}

	public function setEcomark($ecomark){
		$this->ecomark = $ecomark;
	}
	
	public function getId(){
		return $this->id;
	}
	
	public function setId($id){
		$this->id = $id;
	}

	public function getPublicTag(){
		return $this->publicTag;
	}

	public function setPublicTag($publicTag){
		$this->publicTag = $publicTag;
	}

	public function getNumFactories(){
		return $this->numFactories;
	}

	public function setNumFactories($numFactories){
		$this->numFactories = $numFactories;
	}

	public function getCivLevel(){
		return $this->civLevel;
	}

	public function setCivLevel($civLevel){
		$this->civLevel = $civLevel;
	}

	public function getDefBonus(){
		return $this->defBonus;
	}

	public function setDefBonus($defBonus){
		$this->defBonus = $defBonus;
	}

	public function getTrades(){
		return $this->trades;
	}

	public function setTrades($trades){
		$this->trades = $trades;
	}
	
	public function getInfiltrations(){
		return $this->infiltrations;
	}
	
	public function setInfiltrations($infiltrations){
		$this->infiltrations = $infiltrations;
	}
}