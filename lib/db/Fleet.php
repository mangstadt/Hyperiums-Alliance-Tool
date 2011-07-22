<?php
namespace db;

/**
 * A row from the "fleets" table.
 * @author mangstadt
 */
class Fleet{
	public $id;
	
	/**
	 * @var Player
	 */
	public $player;
	
	/**
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
}