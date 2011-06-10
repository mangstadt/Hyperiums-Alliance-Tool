<?php
namespace HAPI;

class Infiltration{
	private $id;
	private $planetName;
	private $planetTag;
	private $planetX;
	private $planetY;
	private $level;
	private $security;
	private $growing;
	private $captive;
	
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

	public function getLevel(){
		return $this->level;
	}

	public function setLevel($level){
		$this->level = $level;
	}

	public function getSecurity(){
		return $this->security;
	}

	public function setSecurity($security){
		$this->security = $security;
	}

	public function isGrowing(){
		return $this->growing;
	}

	public function setGrowing($growing){
		$this->growing = $growing;
	}

	public function isCaptive(){
		return $this->captive;
	}

	public function setCaptive($captive){
		$this->captive = $captive;
	}
}