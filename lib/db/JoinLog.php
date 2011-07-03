<?php
namespace db;

/**
 * A row from the "joinLogs" table.
 * @author mangstadt
 */
class JoinLog{
	const EVENT_REQUESTED = 0;
	const EVENT_ACCEPTED = 1;
	const EVENT_REJECTED = 2;
	const EVENT_CANCELLED = 3;
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