<?php
namespace db;

/**
 * A row from the "joinRequests" table.
 * @author mangstadt
 */
class JoinRequest{
	public $id;
	
	/**
	 * The player who made the request.
	 * @var Player
	 */
	public $player;
	
	/**
	 * The alliance that the player made the request to
	 * @var Alliance
	 */
	public $alliance;
	
	/**
	 * The date the request was made.
	 * @var DateTime
	 */
	public $requestDate;
}