<?php
namespace db;

/**
 * A row from the "joinLogs" table.
 * @author mangstadt
 */
class JoinLog{
	/**
	 * Player requested to be authenticated by an alliance
	 * @var integer
	 */
	const EVENT_REQUESTED = 0;
	
	/**
	 * Player was accepted into the alliance
	 * @var integer
	 */
	const EVENT_ACCEPTED = 1;
	
	/**
	 * Player's authentication request was rejected.
	 * @var integer
	 */
	const EVENT_REJECTED = 2;
	
	/**
	 * Player cancelled his authentication request.
	 * @var integer
	 */
	const EVENT_CANCELLED = 3;
	
	/**
	 * Player was removed from the alliance.
	 * @var integer
	 */
	const EVENT_REMOVED = 4;
	
	public $id;
	
	/**
	 * @var Player
	 */
	public $player;
	
	/**
	 * @var Alliance
	 */
	public $alliance;
	
	public $event;
	
	/**
	 * @var DateTime
	 */
	public $eventDate;
}