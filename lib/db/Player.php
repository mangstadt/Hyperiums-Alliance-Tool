<?php
namespace db;

/**
 * A row from the "players" table.
 * @author mangst
 */
class Player{
	public $id;
	public $name;
	
	/**
	 * The date the player last logged in.
	 * @var DateTime
	 */
	public $lastLoginDate;
	
	/**
	 * The player's IP address when he last logged in.
	 * @var string
	 */
	public $lastLoginIP;
}