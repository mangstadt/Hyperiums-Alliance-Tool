<?php
namespace HAPI;

class IsMsgInfo{
	private $msg;
	private $planet;
	private $report;
	private $military;
	private $trading;
	private $infiltration;
	private $control;
	
	public function isMsg(){
		return $this->msg;
	}

	public function setMsg($msg){
		$this->msg = $msg;
	}

	public function isPlanet(){
		return $this->planet;
	}

	public function setPlanet($planet){
		$this->planet = $planet;
	}

	public function isReport(){
		return $this->report;
	}

	public function setReport($report){
		$this->report = $report;
	}

	public function isMilitary(){
		return $this->military;
	}

	public function setMilitary($military){
		$this->military = $military;
	}

	public function isTrading(){
		return $this->trading;
	}

	public function setTrading($trading){
		$this->trading = $trading;
	}

	public function isInfiltration(){
		return $this->infiltration;
	}

	public function setInfiltration($infiltration){
		$this->infiltration = $infiltration;
	}

	public function isControl(){
		return $this->control;
	}

	public function setControl($control){
		$this->control = $control;
	}
}