<?php
namespace HAPI;

class IsMsg{
	private $msg;
	private $report;
	
	public function isMsg(){
		return $this->msg;
	}

	public function setMsg($msg){
		$this->msg = $msg;
	}

	public function isReport(){
		return $this->report;
	}

	public function setReport($report){
		$this->report = $report;
	}
}