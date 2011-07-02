<?php
namespace db;

use \PDO;
use \DateTime;
use \Exception;

/**
 * Controls all database interaction.
 * @author mangstadt
 */
class HypToolsDao{
	/**
	 * Gives a player submit permissions in an alliance.
	 * @var integer
	 */
	const PERMS_SUBMIT = 1;
	
	/**
	 * Gives a player view permissions in an alliance.
	 * @var integer
	 */
	const PERMS_VIEW = 2;
	
	/**
	 * Gives a player admin permissions in an alliance.
	 * @var integer
	 */
	const PERMS_ADMIN = 4;
	
	/**
	 * The database connection
	 * @var PDO
	 */
	private $db;
	
	/**
	 * The game that the player is logged into.
	 * @var Game
	 */
	private $game;
	
	/**
	 * Creates the DAO.
	 * @param Game $game (optional) the game the player is logged into
	 */
	public function __construct(Game $game = null){
		include __DIR__ . '/db.php';
		$this->db = $db;
		
		//throw exception when a database error occurs instead of just returning "false"
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
		//create the tables (if they don't already exist)
		$sql = file_get_contents(__DIR__ . "/db.sql");
		$sql = preg_replace("~(--.*?\\n)|(/\\*.*?\\*/)~s", "", $sql); //remove comments--PDO throws an error if there are any comments in a SQL query
		$queries = preg_split("/\\s*;\\s*/", $sql); //split by ";"
		foreach ($queries as $query){
			if ($query != ""){ //the last element is empty
				$this->db->exec($query);
			}
		}

		$this->game = $game;
	}
	
	/**
	 * Sets the game that the player is logged into.
	 * @param Game $game the game the player is logged into
	 */
	public function setGame(Game $game){
		$this->game = $game;
	}
	
	/**
	 * Updates or inserts game info.
	 * @param string $name the game name
	 * @param string $description the game description
	 * @return Game the game
	 */
	public function upsertGame($name, $description){
		$game = new Game();
		$game->name = $name;
		$game->description = $description;
		
		$sql = "SELECT gameId FROM games WHERE Ucase(name) = :name";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":name", strtoupper($game->name), PDO::PARAM_STR);
		$stmt->execute();
		if ($row = $stmt->fetch()){
			$game->id = $row[0];
			
			$sql = "
			UPDATE games SET
			name = :name,
			description = :description
			WHERE gameId = :gameId
			";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(":name", $game->name, PDO::PARAM_STR);
			$stmt->bindValue(":description", $game->description, PDO::PARAM_STR);
			$stmt->bindValue(":gameId", $game->id, PDO::PARAM_INT);
			$stmt->execute();
		} else {
			$sql = "
			INSERT INTO games
			( name,  description) VALUES
			(:name, :description)
			";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(":name", $game->name, PDO::PARAM_STR);
			$stmt->bindValue(":description", $game->description, PDO::PARAM_STR);
			$stmt->execute();
			
			$game->id = $this->db->lastInsertId();
		}
		
		return $game;
	}
	
	/**
	 * Selects a player from the database or creates a new row if one doesn't exist.
	 * @param string $name the player name
	 * @param integer $hypPlayerId the Hyperiums player ID
	 * @return Player the player
	 */
	public function upsertPlayer($name, $hypPlayerId = null){
		$player = new Player();
		$player->game = $this->game;
		$player->name = $name;
		$player->hypPlayerId = $hypPlayerId;
		
		$sql = "SELECT playerId FROM players WHERE";
		if ($hypPlayerId === null){
			$sql .= " Ucase(name) = Ucase(:name)";
		} else {
			$sql .= " hypPlayerId = :hypPlayerId";
		}
		$sql .= " AND gameId = :gameId";
		
		$stmt = $this->db->prepare($sql);
		if ($hypPlayerId === null){
			$stmt->bindValue(":name", $name, PDO::PARAM_STR);
		} else {
			$stmt->bindValue(":hypPlayerId", $hypPlayerId, PDO::PARAM_INT);
		}
		$stmt->bindValue(":gameId", $this->game->id, PDO::PARAM_INT);
		$stmt->execute();
		if ($row = $stmt->fetch()){
			$id = $row[0];
			$player->id = $id;
			
			$sql = "
			UPDATE players
			SET name = :name
			WHERE playerId = :playerId
			";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(":name", $name, PDO::PARAM_STR);
			$stmt->bindValue(":playerId", $id, PDO::PARAM_INT);
			$stmt->execute();
		} else {
			$sql = "
			INSERT INTO players
			( name,  hypPlayerId,  gameId, lastLoginDate) VALUES
			(:name, :hypPlayerId, :gameId, NULL)
			";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(":name", $name, PDO::PARAM_STR);
			$stmt->bindValue(":hypPlayerId", $hypPlayerId, PDO::PARAM_INT);
			$stmt->bindValue(":gameId", $this->game->id, PDO::PARAM_INT);
			$stmt->execute();
			
			$player->id = $this->db->lastInsertId();
		}
		
		return $player;
	}
	
	/**
	 * Updates the player's last login information.
	 * @param Player $player the player
	 */
	public function updatePlayerLastLogin(Player $player){
		$sql = "
		UPDATE players SET
		lastLoginDate = Now()
		WHERE playerId = :playerId
		";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":playerId", $player->id, PDO::PARAM_INT);
		$stmt->execute();
		
		//update object
		$player->lastLoginDate = new DateTime("now");
	}
	
	/**
	 * Determines whether an alliance exists or not.
	 * @param string $tag the alliance tag
	 * @return boolean true if the alliance exists, false if not
	 */
	public function doesAllianceExist($tag){
		$sql = "
		SELECT Count(*) FROM alliances
		WHERE Ucase(tag) = :tag
		AND gameId = :gameId
		";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":tag", strtoupper($tag), PDO::PARAM_STR);
		$stmt->bindValue(":gameId", $this->game->id, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch();
		return $row[0] > 0;
	}
	
	/**
	 * Updates an alliance or inserts it if it doesn't exist.
	 * @param string $tag the alliance's tag
	 * @param string $name the alliance's name
	 * @param string $president the name of the president
	 */
	public function upsertAlliance($tag, $name, $president){
		$presPlayer = $this->upsertPlayer($president);
		
		$allianceId = $this->selectAllianceId($tag);
		if ($allianceId !== null){
			$sql = "
			UPDATE alliances SET
			name = :name,
			president = :president
			WHERE allianceId = :allianceId
			";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(":name", $name, PDO::PARAM_STR);
			$stmt->bindValue(":president", $presPlayer->id, PDO::PARAM_INT);
			$stmt->bindValue(":allianceId", $allianceId, PDO::PARAM_INT);
			$stmt->execute();
		} else {
			$sql = "
			INSERT INTO alliances
			( tag,  name,  president,  gameId, registeredDate,  motd) VALUES
			(:tag, :name, :president, :gameId, NULL,            NULL)
			";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(":tag", $tag, PDO::PARAM_STR);
			$stmt->bindValue(":name", $name, PDO::PARAM_STR);
			$stmt->bindValue(":president", $presPlayer->id, PDO::PARAM_INT);
			$stmt->bindValue(":gameId", $this->game->id, PDO::PARAM_INT);
			$stmt->execute();
		}
	}
	
	/**
	 * Determines if a player has made a request to join an alliance.
	 * @param Player $player the player
	 * @param Alliance $alliance the alliance
	 * @return boolean true if the player has requested to join the alliance
	 */
	public function hasPlayerMadeJoinRequest(Player $player, Alliance $alliance){
		$sql = "
		SELECT Count(*) FROM joinRequests
		WHERE playerId = :playerId
		AND allianceId = :allianceId
		";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":playerId", $player->id, PDO::PARAM_INT);
		$stmt->bindValue(":allianceId", $alliance->id, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch();
		return $row[0] > 0;
	}
	
	/**
	 * Gets the player's join requests
	 * @param Player $player the player
	 * @param string $orderBy (optional) sort the permissions by this column
	 * @return array(JoinRequest) the player's join requests
	 */
	public function selectJoinRequestsByPlayer(Player $player, $orderBy = null){
		$joinRequests = array();
		
		$sql = "
		SELECT j.*,
		a.*, a.name AS allianceName,
		p.*, p.name AS playerName,
		p2.*, p2.playerId AS presidentId, p2.name AS presidentName, p2.lastLoginDate AS presidentLastLoginDate,
		g.*, g.name AS gameName, g.description AS gameDescription
		FROM joinRequests j
		INNER JOIN alliances a ON j.allianceID = a.allianceID
		INNER JOIN players p ON j.playerId = p.playerId
		INNER JOIN players p2 ON a.president = p2.playerId
		INNER JOIN games g ON p.gameId = g.gameId
		WHERE j.playerId = :playerId
		";
		if ($orderBy !== null){
			$sql .= " ORDER BY $orderBy";
		}
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":playerId", $player->id, PDO::PARAM_INT);
		$stmt->execute();
		while ($row = $stmt->fetch()){
			$joinRequest = new JoinRequest();
			$joinRequest->id = $row['joinRequestId'];
			$joinRequest->requestDate = $this->date($row['requestDate']);
			
			$game = new Game();
			$game->id = $row['gameId'];
			$game->name = $row['gameName'];
			$game->description = $row['gameDescription'];
			
			$player = new Player();
			$player->id = $row['playerId'];
			$player->name = $row['playerName'];
			$player->lastLoginDate = $this->date($row['lastLoginDate']);
			$player->game = $game;
			$joinRequest->player = $player;
			
			$president = new Player();
			$president->id = $row['presidentId'];
			$president->name = $row['presidentName'];
			$president->lastLoginDate = $this->date($row['presidentLastLoginDate']);
			$president->game = $game;
			
			$alliance = new Alliance();
			$alliance->id = $row['allianceId'];
			$alliance->name = $row['allianceName'];
			$alliance->tag = $row['tag'];
			$alliance->registeredDate = $this->date($row['registeredDate']);
			$alliance->motd = $row['motd'];
			$alliance->game = $game;
			$alliance->president = $president;
			$joinRequest->alliance = $alliance;
			
			$joinRequests[] = $joinRequest;
		}
		
		return $joinRequests;
	}
	
	/**
	 * Gets the player's permissions
	 * @param Player $player the player
	 * @param string $orderBy (optional) sort the permissions by this column
	 * @return array(Permission) the player's permissions
	 */
	public function selectPermissionsByPlayer(Player $player, $orderBy = null){
		$sql = "
		SELECT p.*,
		a.*, a.name AS allianceName,
		pl.*, pl.name AS playerName,
		pl2.playerId AS presidentId, pl2.name AS presidentName, pl2.lastLoginDate AS presidentLastLoginDate,
		g.*, g.name AS gameName, g.description AS gameDescription
		FROM permissions p
		INNER JOIN players pl ON p.playerId = pl.playerId
		INNER JOIN games g ON g.gameID = pl.gameId
		INNER JOIN alliances a ON p.allianceId = a.allianceId
		INNER JOIN players pl2 ON a.president = pl2.playerId
		WHERE p.playerId = :playerId
		";
		if ($orderBy !== null){
			$sql .= " ORDER BY $orderBy";
		}
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":playerId", $player->id, PDO::PARAM_INT);
		$stmt->execute();
		$permissions = array();
		while ($row = $stmt->fetch()){
			$permission = new Permission();
			$permission->id = $row['permissionId'];
			$permission->joinDate = $this->date($row['joinDate']);
			$perms = $row['perms'];
			$permission->permSubmit = $this->bitmask($perms, self::PERMS_SUBMIT);
			$permission->permView = $this->bitmask($perms, self::PERMS_VIEW);
			$permission->permAdmin = $this->bitmask($perms, self::PERMS_ADMIN);
			
			$game = new Game();
			$game->id = $row['gameId'];
			$game->name = $row['gameName'];
			$game->description = $row['gameDescription'];
			
			$player = new Player();
			$player->id = $row['playerId'];
			$player->name = $row['playerName'];
			$player->lastLoginDate = $this->date($row['lastLoginDate']);
			$player->game = $game;
			$permission->player = $player;
			
			$president = new Player();
			$president->id = $row['presidentId'];
			$president->name = $row['presidentName'];
			$president->lastLoginDate = $this->date($row['presidentLastLoginDate']);
			$president->game = $game;
			
			$alliance = new Alliance();
			$alliance->id = $row['allianceId'];
			$alliance->name = $row['allianceName'];
			$alliance->tag = $row['tag'];
			$alliance->registeredDate = $this->date($row['registeredDate']);
			$alliance->motd = $row['motd'];
			$alliance->game = $game;
			$alliance->president = $president;
			$permission->alliance = $alliance;

			$permissions[] = $permission;
		}
		return $permissions;
	}
	
	/**
	 * Determines if a player belongs to an alliance.
	 * @param Player $player the player
	 * @param Alliance $alliance the alliance
	 * @return boolean true if the player belongs to the alliance
	 */
	public function doesPlayerBelongToAlliance(Player $player, Alliance $alliance){
		$sql = "
		SELECT Count(*) FROM permissions
		WHERE playerId = :playerId
		AND allianceId = :allianceId
		";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":playerId", $player->id, PDO::PARAM_INT);
		$stmt->bindValue(":allianceId", $alliance->id, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch();
		return $row[0] > 0;
	}
	
	/**
	 * Make a request for a player to join an alliance.
	 * @param Player $player the player
	 * @param string $tag the alliance tag
	 */
	public function insertJoinRequest(Player $player, Alliance $alliance){
		$sql = "
		INSERT INTO joinRequests
		( playerId,  allianceId, requestDate) VALUES
		(:playerId, :allianceId, Now())
		";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":playerId", $player->id, PDO::PARAM_INT);
		$stmt->bindValue(":allianceId", $alliance->id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	/**
	 * Gives an alliance president full access to his alliance.
	 * @param Player $player the player
	 * @param Alliance $alliance the alliance
	 */
	public function insertPresidentPermission(Player $president, Alliance $alliance){
		$sql = "
		INSERT INTO permissions
		( playerId,  allianceId,  perms, joinDate) VALUES
		(:playerId, :allianceId, :perms, Now())
		";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":playerId", $president->id, PDO::PARAM_INT);
		$stmt->bindValue(":allianceId", $alliance->id, PDO::PARAM_INT);
		$stmt->bindValue(":perms", self::PERMS_SUBMIT | self::PERMS_VIEW | self::PERMS_ADMIN, PDO::PARAM_INT);
		$stmt->execute();
		
		$sql = "UPDATE alliances SET registeredDate = Now() WHERE allianceId = :allianceId";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":allianceId", $alliance->id, PDO::PARAM_INT);
		$stmt->execute();
		
		//update object
		$alliance->registeredDate = new DateTime("now");
	}
	
	/**
	 * Gets an alliance.
	 * @param string $tag the alliance's tag
	 * @return Alliance the alliance
	 */
	public function selectAllianceByTag($tag){
		$alliance = null;
		
		$sql = "
		SELECT a.*, p.*, p.name AS playerName
		FROM alliances a INNER JOIN players p ON a.president = p.playerId
		WHERE Ucase(a.tag) = :tag
		AND a.gameId = :gameId
		";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":tag", strtoupper($tag), PDO::PARAM_STR);
		$stmt->bindValue(":gameId", $this->game->id, PDO::PARAM_INT);
		$stmt->execute();
		if ($row = $stmt->fetch()){
			$alliance = new Alliance();
			$alliance->id = $row['allianceId'];
			$alliance->tag = $row['tag'];
			$alliance->name = $row['name'];
			$alliance->registeredDate = $this->date($row['registeredDate']);
			$alliance->motd = $row['motd'];
			$alliance->game = $this->game;
			
			$president = new Player();
			$president->id = $row["playerId"];
			$president->name = $row["playerName"];
			$president->lastLoginDate = $this->date($row["lastLoginDate"]);
			$president->game = $this->game;
			$alliance->president = $president;
		}
		
		return $alliance;
	}
	
	/**
	 * Wipes the database.
	 */
	public function dropAllTables(){
		$this->beginTransaction();
		try{
			$tables = array("permissions", "joinRequests", "alliances", "players", "games");
			foreach ($tables as $t){
				$this->db->exec("DROP TABLE IF EXISTS $t");
			}
			$this->commit();
		} catch (Exception $e){
			$this->rollBack();
			throw $e;
		}
	}
	
	/**
	 * Gets an alliance's ID.
	 * @param string $tag the alliance tag
	 * @return integer the alliance's ID or null if not found
	 */
	private function selectAllianceId($tag){
		$sql = "
		SELECT allianceId FROM alliances
		WHERE Ucase(tag) = :tag
		AND gameId = :gameId
		";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":tag", strtoupper($tag), PDO::PARAM_STR);
		$stmt->bindValue(":gameId", $this->game->id, PDO::PARAM_INT);
		$stmt->execute();
		if ($row = $stmt->fetch()){
			return $row[0];
		}
		return null;
	}
	
	/**
	 * Starts a database transaction.
	 */
	public function beginTransaction(){
		$this->db->beginTransaction();
	}
	
	/**
	 * Commits a database transaction.
	 */
	public function commit(){
		$this->db->commit();
	}
	
	/**
	 * Rollsback a database transaction.
	 */
	public function rollBack(){
		$this->db->rollBack();
	}
	
	/**
	 * Gets the value of a boolean column.
	 * @param mixed $value the column value
	 * @return boolean true if the column value is true, false if not
	 */
	private function bool($value){
		return $value != 0;
	}
	
	/**
	 * Gets the value of a date column.
	 * @param mixed $value the column value
	 * @return DateTime the date or null if the column value is null
	 */
	private function date($value){
		if ($value == null){
			return null;
		}
		return new DateTime($value);
	}
	
	/**
	 * Determines if a bitmask has a particular bit value.
	 * @param integer $haystack the bitmask
	 * @param integer $needle the bit to look for
	 * @return boolean true if the bitmask has the bit, false if not
	 */
	private function bitmask($haystack, $needle){
		return ($haystack & $needle) == $needle;	
	}
}
