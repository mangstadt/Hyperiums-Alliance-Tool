<?php
namespace HAPI;

class MovingFleet{
	private $id;
	private $name;
	private $from;
	private $to;
	private $distance;
	private $delay;
	private $defending;
	private $autoDropping;
	private $camouflaged;
	private $bombing;
	private $race;
	private $bombers;
	private $destroyers;
	private $cruisers;
	private $scouts;
	private $armies;
	
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

	public function getFrom(){
		return $this->from;
	}

	public function setFrom($from){
		$this->from = $from;
	}

	public function getTo(){
		return $this->to;
	}

	public function setTo($to){
		$this->to = $to;
	}

	public function getDistance(){
		return $this->distance;
	}

	public function setDistance($distance){
		$this->distance = $distance;
	}

	public function getDelay(){
		return $this->delay;
	}

	public function setDelay($delay){
		$this->delay = $delay;
	}

	public function isDefending(){
		return $this->defending;
	}

	public function setDefending($defending){
		$this->defending = $defending;
	}

	public function isAutoDropping(){
		return $this->autoDropping;
	}

	public function setAutoDropping($autoDropping){
		$this->autoDropping = $autoDropping;
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

	public function getRace(){
		return $this->race;
	}

	public function setRace($race){
		$this->race = $race;
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

	public function getCruisers(){
		return $this->cruisers;
	}

	public function setCruisers($cruisers){
		$this->cruisers = $cruisers;
	}

	public function getScouts(){
		return $this->scouts;
	}

	public function setScouts($scouts){
		$this->scouts = $scouts;
	}

	public function getArmies(){
		return $this->armies;
	}

	public function setArmies($armies){
		$this->armies = $armies;
	}
}