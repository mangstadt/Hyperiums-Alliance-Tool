<?php
namespace db;

/**
 * A row from the "reports" table.
 * @author mangstadt
 */
class Report{
	public $id;
	
	/**
	 * The player that this report is from.
	 * @var Player
	 */
	public $player;
	
	/**
	 * The date this report was submitted.
	 * @var DateTime
	 */
	public $submitDate;
	
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
	
	public $factories = 0;
	
	public $exploits = 0;
	
	/**
	 * The infiltrations in the report.
	 * @var array(Infiltration)
	 */
	public $infiltrations = array();
}