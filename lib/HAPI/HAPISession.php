<?php
namespace HAPI;

/**
 * A HAPI session.
 * @author mangst
 */
class HAPISession{
	/**
	 * The game ID.
	 * @var integer
	 */
	private $gameId;
	
	/**
	 * The player ID.
	 * @var integer
	 */
	private $playerId;
	
	/**
	 * The authentication key.
	 * @var string
	 */
	private $authKey;
	
	/**
	 * The time that the session was created (server time).
	 * @var integer
	 */
	private $creationTime;
	
	/**
	 * Creates a new session object.
	 * @param integer $gameId the game ID
	 * @param integer $playerId the player ID
	 * @param string $authKey the authentication key
	 * @param integer $creationTime the time that the session was created (server time, timestamp)
	 */
	public function __construct($gameId, $playerId, $authKey, $creationTime = null){
		$this->gameId = $gameId;
		$this->playerId = $playerId;
		$this->authKey = $authKey;
		$this->creationTime = $creationTime;
	}
	
	/**
	 * Gets the game ID.
	 * @return integer the game ID.
	 */
	public function getGameId(){
		return $this->gameId;
	}

	/**
	 * Gets the player ID.
	 * @return integer the player ID
	 */
	public function getPlayerId(){
		return $this->playerId;
	}

	/**
	 * Gets the authentication key.
	 * @return string the authentication key
	 */
	public function getAuthKey(){
		return $this->authKey;
	}

	/**
	 * Gets the time that the session was created (server time).
	 * @return integer the session creation time (timestamp)
	 */
	public function getCreationTime(){
		return $this->creationTime;
	}
}