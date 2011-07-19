<?php
namespace db;

/**
 * Interface for data access layer.
 * @author mangstadt
 */
interface HypToolsDao{
	/**
	 * Sets the game that the player is logged into.
	 * @param Game $game the game the player is logged into
	 */
	public function setGame(Game $game);
	
	/**
	 * Updates or inserts game info.
	 * @param string $name the game name
	 * @param string $description the game description
	 * @return Game the game
	 */
	public function upsertGame($name, $description);
	
	/**
	 * Inserts the player if he doesn't exist or updates the player with the most recent info if he does
	 * @param string $name the player name
	 * @param integer $hypPlayerId (optional) the Hyperiums player ID
	 * @return Player the player that was inserted/updated
	 */
	public function upsertPlayer($name, $hypPlayerId = null);
	
	/**
	 * Updates the player's last login information.
	 * @param Player $player the player
	 */
	public function updatePlayerLastLogin(Player $player);
	
	/**
	 * Determines whether an alliance exists or not.
	 * @param string $tag the alliance tag
	 * @return boolean true if the alliance exists, false if not
	 */
	public function doesAllianceExist($tag);
	
	/**
	 * Gets an alliance.
	 * @param string $tag the alliance's tag
	 * @return Alliance the alliance
	 */
	public function selectAllianceByTag($tag);
	
	/**
	 * Updates an alliance or inserts it if it doesn't exist.
	 * @param string $tag the alliance's tag
	 * @param string $name the alliance's name
	 * @param string $president the name of the president
	 */
	public function upsertAlliance($tag, $name, $president);
	
	/**
	 * Deletes a join request.
	 * @param integer $id the join request ID
	 */
	public function deleteJoinRequest($id);
	
	/**
	 * Deletes a join request.
	 * @param Player $player the player who made the join request
	 * @param Alliance $alliance the alliance that the player made the request to
	 */
	public function deleteJoinRequestByPlayerAndAlliance(Player $player, Alliance $alliance);
	
	/**
	 * Determines if a player has made a request to join an alliance.
	 * @param Player $player the player
	 * @param Alliance $alliance the alliance
	 * @return boolean true if the player has requested to join the alliance
	 */
	public function hasPlayerMadeJoinRequest(Player $player, Alliance $alliance);
	
	/**
	 * Make a request for a player to join an alliance.
	 * @param Player $player the player
	 * @param string $tag the alliance tag
	 */
	public function insertJoinRequest(Player $player, Alliance $alliance);
	
	/**
	 * Gets the join requests that were made to an alliance.
	 * @param Alliance $alliance the alliance
	 * @return array(JoinRequest) the join requests that were made to the alliance
	 */
	public function selectJoinRequestsByAlliance(Alliance $alliance);
	
	/**
	 * Gets a join request.
	 * @param integer $id the join request ID
	 * @return JoinRequest the join request or null if not found
	 */
	public function selectJoinRequestById($id);
	
	/**
	 * Gets the player's join requests.
	 * @param Player $player the player
	 * @return array(JoinRequest) the player's join requests
	 */
	public function selectJoinRequestsByPlayer(Player $player);
	
	/**
	 * Deletes a permission.
	 * @param integer $id the permission ID
	 */
	public function deletePermission($id);
	
	/**
	 * Determines if a player belongs to an alliance.
	 * @param Player $player the player
	 * @param Alliance $alliance the alliance
	 * @return boolean true if the player belongs to the alliance
	 */
	public function doesPlayerBelongToAlliance(Player $player, Alliance $alliance);
	
	/**
	 * Inserts a new permission.
	 * @param Permission $permission the permission to insert
	 */
	public function insertPermission(Permission $permission);
	
	/**
	 * Gives an alliance president full access to his alliance.
	 * @param Player $player the player
	 * @param Alliance $alliance the alliance
	 */
	public function insertPresidentPermission(Player $president, Alliance $alliance);
	
	/**
	 * Gets all members of an alliance.
	 * @param Alliance $alliance the alliance
	 * @return array(Permission) the members of the alliance
	 */
	public function selectPermissionsByAlliance(Alliance $alliance);
	
	/**
	 * Gets a permission.
	 * @param integer $id the ID
	 * @return Permission the permission or null if not found
	 */
	public function selectPermissionById($id);
	
	/**
	 * Gets the player's permissions
	 * @param Player $player the player
	 * @return array(Permission) the player's permissions
	 */
	public function selectPermissionsByPlayer(Player $player);
	
	/**
	 * Gets the permissions the player has for an alliance.
	 * @param Player $player the player
	 * @param Alliance $alliance the alliance
	 * @return Permission the player's permissions in the given alliance or null if the player does not belong to the alliance
	 */
	public function selectPermissionsByPlayerAndAlliance(Player $player, Alliance $alliance);
	
	/**
	 * Updates a new permission.
	 * @param Permission $permission the permission to update
	 */
	public function updatePermission(Permission $permission);
	
	/**
	 * Inserts a new join log.
	 * @param Player $player the player
	 * @param Alliance $alliance the alliance
	 * @param integer $event the event (see JoinLog::EVENT_*)
	 */
	public function insertJoinLog(Player $player, Alliance $alliance, $event);
	
	/**
	 * Gets all the join logs that belong to a player.
	 * @param Player $player the player
	 * @return array(JoinLog) the join logs
	 */
	public function selectJoinLogsByPlayer(Player $player);
	
	/**
	 * Deletes all fleet reports belonging to a player.
	 * @param Player $player the player
	 */
	public function deleteFleetsByPlayer(Player $player);
	
	/**
	 * Inserts a new fleet report.
	 * @param Fleet $fleet the fleet report
	 */
	public function insertFleet(Fleet $fleet);
	
	/**
	 * Inserts a new submit log.
	 * @param Player $player the player who submitted the report
	 */
	public function insertSubmitLog(Player $player);
	
	/**
	 * Gets the last submit log entry of the last submission the player made.
	 * @param Player $player the player
	 * @return SubmitLog the last submit log entry or null if the player never submitted a report
	 */
	public function selectLastPlayerSubmitLog(Player $player);
	
	/**
	 * Wipes the database.
	 */
	public function dropAllTables();
	
	/**
	 * Starts a database transaction.
	 */
	public function beginTransaction();
	
	/**
	 * Commits a database transaction.
	 */
	public function commit();
	
	/**
	 * Rollsback a database transaction.
	 */
	public function rollBack();
}
