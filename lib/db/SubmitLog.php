<?php
namespace db;

/**
 * A row from the "submitLogs" table.
 * @author mangstadt
 */
class SubmitLog{
	public $id;
	
	/**
	 * 
	 * @var Player
	 */
	public $player;
	
	/**
	 * 
	 * @var DateTime
	 */
	public $submitDate;
}