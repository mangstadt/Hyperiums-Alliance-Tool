<?php
namespace hapidao;

/**
 * Interface for making calls to the HAPI webservice.
 * @author mangstadt
 */
interface HypToolsHapiDao {
	/**
	 * Sets the data that identifies the current player.
	 * @param mixed $data
	 */
	public function setPlayerIdentifier($data);
	
	/**
	 * Gets all Hyperiums games.
	 * @return array(HAPI\Game) the games
	 */
	public function getGames();
	
	/**
	 * Gets the fleets info of the player.
	 * @return array(HAPI\FleetsInfo) the fleets info
	 */
	public function getFleetsInfo();
	
	/**
	 * Gets info on each of the player's controlled planets.
	 * @return array(HAPI\PlanetInfo) the planet info
	 */
	public function getPlanetInfo();
}