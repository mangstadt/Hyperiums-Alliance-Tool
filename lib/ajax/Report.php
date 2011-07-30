<?php
namespace ajax;

/**
 * Represents an AJAX response to the report-generation request.
 * @author mangst
 */
class Report{
	public $azterkScouts = 0;
	public $azterkBombers = 0;
	public $azterkDestroyers = 0;
	public $azterkCruisers = 0;
	public $azterkArmies = 0;
	
	public $humanScouts = 0;
	public $humanBombers = 0;
	public $humanDestroyers = 0;
	public $humanCruisers = 0;
	public $humanArmies = 0;
	
	public $xillorScouts = 0;
	public $xillorBombers = 0;
	public $xillorDestroyers = 0;
	public $xillorCruisers = 0;
	public $xillorArmies = 0;
	
	public $avgSpaceP = 0;
	public $avgGroundP = 0;
	
	public $factories = 0;
	
	public $exploits = 0;
}