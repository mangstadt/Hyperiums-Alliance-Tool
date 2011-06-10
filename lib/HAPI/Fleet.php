<?php
namespace HAPI;

class Fleet{
	private $id;
	private $name;
	private $sellPrice;
	private $race;
	private $owner;
	private $defending;
	private $camouflaged;
	private $bombing;
	private $autoDropping;
	private $delay;
	private $groundArmies;
	private $scouts;
	private $cruisers;
	private $bombers;
	private $destroyers;
	private $carriedArmies;
	
	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
	}

	public function getName(){
		return $this->name;
	}

	public function setName($name){
		$this->name = $name;
	}

	public function getSellPrice(){
		return $this->sellPrice;
	}

	public function setSellPrice($sellPrice){
		$this->sellPrice = $sellPrice;
	}

	public function getRace(){
		return $this->race;
	}

	public function setRace($race){
		$this->race = $race;
	}

	public function getOwner(){
		return $this->owner;
	}

	public function setOwner($owner){
		$this->owner = $owner;
	}

	public function isDefending(){
		return $this->defending;
	}

	public function setDefending($defending){
		$this->defending = $defending;
	}

	public function isCamouflaged(){
		return $this->camouflaged;
	}

	public function setCamouflaged($camouflaged){
		$this->camouflaged = $camouflaged;
	}

	public function isBombing(){
		return $this->bombing;
	}

	public function setBombing($bombing){
		$this->bombing = $bombing;
	}

	public function isAutoDropping(){
		return $this->autoDropping;
	}

	public function setAutoDropping($autoDropping){
		$this->autoDropping = $autoDropping;
	}

	public function getDelay(){
		return $this->delay;
	}

	public function setDelay($delay){
		$this->delay = $delay;
	}

	public function getGroundArmies(){
		return $this->groundArmies;
	}

	public function setGroundArmies($groundArmies){
		$this->groundArmies = $groundArmies;
	}

	public function getScouts(){
		return $this->scouts;
	}

	public function setScouts($scouts){
		$this->scouts = $scouts;
	}

	public function getCruisers(){
		return $this->cruisers;
	}

	public function setCruisers($cruisers){
		$this->cruisers = $cruisers;
	}

	public function getBombers(){
		return $this->bombers;
	}

	public function setBombers($bombers){
		$this->bombers = $bombers;
	}

	public function getDestroyers(){
		return $this->destroyers;
	}

	public function setDestroyers($destroyers){
		$this->destroyers = $destroyers;
	}

	public function getCarriedArmies(){
		return $this->carriedArmies;
	}

	public function setCarriedArmies($carriedArmies){
		$this->carriedArmies = $carriedArmies;
	}
}