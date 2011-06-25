<?php
namespace db;

/**
 * A row from the "alliances" table.
 * @author mangstadt
 */
class Alliance{
	public $allianceId;
	public $tag;
	public $name;
	
	/**
	 * The alliance's president.
	 * @var Player
	 */
	public $president;
	
	/**
	 * The date that the alliance president joined the alliance (on hyp tools).
	 * @var DateTime
	 */
	public $registeredDate;
	
	/**
	 * The message of the day.
	 * @var string
	 */
	public $motd;
}