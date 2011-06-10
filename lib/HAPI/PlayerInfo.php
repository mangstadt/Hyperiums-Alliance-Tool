<?php
namespace HAPI;

class PlayerInfo{
	private $name;
	private $hypRank;
	private $rankinf;
	private $scoreinf;
	private $cash;
	private $rankfin;
	private $scorefin;
	private $rankpow;
	private $scorepow;
	private $planets;
	private $lastIncome;
	
	public function getName(){
		return $this->name;
	}

	public function setName($name){
		$this->name = $name;
	}

	public function getHypRank(){
		return $this->hypRank;
	}

	public function setHypRank($hypRank){
		$this->hypRank = $hypRank;
	}

	public function getRankinf(){
		return $this->rankinf;
	}

	public function setRankinf($rankinf){
		$this->rankinf = $rankinf;
	}

	public function getScoreinf(){
		return $this->scoreinf;
	}

	public function setScoreinf($scoreinf){
		$this->scoreinf = $scoreinf;
	}

	public function getCash(){
		return $this->cash;
	}

	public function setCash($cash){
		$this->cash = $cash;
	}

	public function getRankfin(){
		return $this->rankfin;
	}

	public function setRankfin($rankfin){
		$this->rankfin = $rankfin;
	}

	public function getScorefin(){
		return $this->scorefin;
	}

	public function setScorefin($scorefin){
		$this->scorefin = $scorefin;
	}

	public function getRankpow(){
		return $this->rankpow;
	}

	public function setRankpow($rankpow){
		$this->rankpow = $rankpow;
	}

	public function getScorepow(){
		return $this->scorepow;
	}

	public function setScorepow($scorepow){
		$this->scorepow = $scorepow;
	}

	public function getPlanets(){
		return $this->planets;
	}

	public function setPlanets($planets){
		$this->planets = $planets;
	}

	public function getLastIncome(){
		return $this->lastIncome;
	}

	public function setLastIncome($lastIncome){
		$this->lastIncome = $lastIncome;
	}
}