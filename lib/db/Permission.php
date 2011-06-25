<?php
namespace db;

/**
 * A row from the "permissions" table.
 * @author mangstadt
 */
class Permission{
	const STATUS_REQUESTED = 0;
	const STATUS_ACCEPTED = 1;
	const STATUS_REJECTED = 2;
	
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
	 * See Permission::STATUS_* constants.
	 * @var integer
	 */
	public $status;
	
	/**
	 * The date the join request was sent.
	 * @var DateTime
	 */
	public $requestDate;
	
	/**
	 * The date the join request was accepted or rejected.
	 * @var DateTime
	 */
	public $acceptDate;
	
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