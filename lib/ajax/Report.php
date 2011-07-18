<?php
namespace ajax;

/**
 * Represents an AJAX response to the report-generation request.
 * @author mangst
 */
class Report{
	public $azterkScouts;
	public $azterkBombers;
	public $azterkDestroyers;
	public $azterkCruisers;
	public $azterkArmies;
	
	public $humanScouts;
	public $humanBombers;
	public $humanDestroyers;
	public $humanCruisers;
	public $humanArmies;
	
	public $xillorScouts;
	public $xillorBombers;
	public $xillorDestroyers;
	public $xillorCruisers;
	public $xillorArmies;
	
	public $avgSpaceP;
	public $avgGroundP;
}