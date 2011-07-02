<?php
namespace db;

/**
 * A row from the "permissions" table.
 * @author mangstadt
 */
class Permission{
	public $id;
	
	/**
	 * @var Player
	 */
	public $player;
	
	/**
	 * @var Alliance
	 */
	public $alliance;
	
	/**
	 * The date the player joined the alliance.
	 * @var DateTime
	 */
	public $joinDate;
	
	/**
	 * True if the player has permission to submit info to the alliance.
	 * @var boolean
	 */
	public $permSubmit;
	
	/**
	 * True if the player has permission to view info that was submitted by alliance members.
	 * @var boolean
	 */
	public $permView;
	
	/**
	 * True if the player has permission to accept/reject new players and change their permissions.
	 * @var boolean
	 */
	public $permAdmin;
}