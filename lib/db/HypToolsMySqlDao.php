<?php
namespace db;

use \PDO;
use \DateTime;
use \Exception;
use \Env;

/**
 * Controls all database interaction.
 * @author mangstadt
 */
class HypToolsMySqlDao implements HypToolsDao{
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
		$this->db = Env::dbConnect();
		
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

	//override
	public function setGame(Game $game){
		$this->game = $game;
	}

	//override
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
	
	//override
	public function upsertPlayer($name, $hypPlayerId = null){
		$player = new Player();
		$player->game = $this->game;
		$player->name = $name;
		$player->hypPlayerId = $hypPlayerId;
		
		//search for player using hypPlayerId
		if ($hypPlayerId != null){
			$sql = "
			SELECT playerId FROM players
			WHERE hypPlayerId = :hypPlayerId
			AND gameId = :gameId
			";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(":hypPlayerId", $hypPlayerId, PDO::PARAM_INT);
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
				
				return $player;
			}
		}
		
		//search for player using name
		$sql = "
		SELECT playerId FROM players
		WHERE Ucase(name) = Ucase(:name)
		AND gameId = :gameId
		";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":name", $name, PDO::PARAM_STR);
		$stmt->bindValue(":gameId", $this->game->id, PDO::PARAM_INT);
		$stmt->execute();

		if ($row = $stmt->fetch()){
			$id = $row[0];
			$player->id = $id;
			//no need to run an update because the name doesn't need to be updated
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

	//override
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
	
	//override
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
	
	//override
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
	
	//override
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
	 * Gets a list of join requests.
	 * @param string $column the name of the column to include in the "WHERE" clause
	 * @param string $value the value of the column in the "WHERE" clause
	 * @param integer $pdoType the PDO type of the column
	 * @param string $orderBy (optional) the ORDER BY clause
	 * @return array(JoinRequest) the join requests
	 */
	private function selectJoinRequests($column, $value, $pdoType, $orderBy = null){
		$joinRequests = array();
		
		$sql = "
		SELECT j.*,
		a.*, a.name AS allianceName,
		p.*, p.playerId AS thePlayerId, p.name AS playerName,
		p2.*, p2.playerId AS presidentId, p2.name AS presidentName, p2.lastLoginDate AS presidentLastLoginDate,
		g.*, g.name AS gameName, g.description AS gameDescription
		FROM joinRequests j
		INNER JOIN alliances a ON j.allianceID = a.allianceID
		INNER JOIN players p ON j.playerId = p.playerId
		INNER JOIN players p2 ON a.president = p2.playerId
		INNER JOIN games g ON p.gameId = g.gameId
		WHERE j.$column = :$column
		";
		if ($orderBy !== null){
			$sql .= " ORDER BY $orderBy";
		}
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":$column", $value, $pdoType);
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
			$player->id = $row['thePlayerId'];
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
	
	//override
	public function selectJoinRequestsByPlayer(Player $player){
		return $this->selectJoinRequests("playerId", $player->id, PDO::PARAM_INT, "j.requestDate DESC");
	}
	
	//override
	public function selectJoinRequestsByAlliance(Alliance $alliance){
		return $this->selectJoinRequests("allianceId", $alliance->id, PDO::PARAM_INT, "j.requestDate");
	}
	
	//override
	public function selectJoinRequestById($id){
		$ret = $this->selectJoinRequests("joinRequestId", $id, PDO::PARAM_INT);
		if (count($ret) == 1){
			return $ret[0];
		}
		return null;
	}
	
	//override
	public function deleteJoinRequest($id){
		$sql = "DELETE FROM joinRequests WHERE joinRequestId = :joinRequestId";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":joinRequestId", $id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	//override
	public function deleteJoinRequestByPlayerAndAlliance(Player $player, Alliance $alliance){
		$sql = "
		DELETE FROM joinRequests
		WHERE playerId = :playerId
		AND allianceId = :allianceId
		";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":playerId", $player->id, PDO::PARAM_INT);
		$stmt->bindValue(":allianceId", $alliance->id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	/**
	 * Gets a list of permissions.
	 * @param array(array) $where the list of where clauses (index 0 = column name, 1 = column value, 2 = PDO type)
	 * @param string $orderBy (optional) the ORDER BY clause
	 * @return array(Permission) the permissions
	 */
	private function selectPermissions(array $where, $orderBy = null){
		$sql = "
		SELECT p.*,
		a.*, a.name AS allianceName,
		pl.*, pl.playerId AS thePlayerId, pl.name AS playerName,
		pl2.playerId AS presidentId, pl2.name AS presidentName, pl2.lastLoginDate AS presidentLastLoginDate,
		g.*, g.name AS gameName, g.description AS gameDescription
		FROM permissions p
		INNER JOIN players pl ON p.playerId = pl.playerId
		INNER JOIN games g ON g.gameID = pl.gameId
		INNER JOIN alliances a ON p.allianceId = a.allianceId
		INNER JOIN players pl2 ON a.president = pl2.playerId
		";
		if (count($where) > 0){
			$sql .= " WHERE p.{$where[0][0]} = :{$where[0][0]}";
			for ($i = 1; $i < count($where); $i++){
				$sql .= " AND p.{$where[$i][0]} = :{$where[$i][0]}";
			}
		}
		if ($orderBy !== null){
			$sql .= " ORDER BY $orderBy";
		}
		$stmt = $this->db->prepare($sql);
		foreach ($where as $w){
			$stmt->bindValue(":" . $w[0], $w[1], $w[2]);
		}
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
			$player->id = $row['thePlayerId'];
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
	
	//override
	public function selectPermissionById($id){
		$where = array(
			array("permissionId", $id, PDO::PARAM_INT)
		);
		$ret = $this->selectPermissions($where);
		if (count($ret) == 0){
			return null;
		}
		return $ret[0];
	}
	
	//override
	public function selectPermissionsByPlayer(Player $player){
		$where = array(
			array("playerId", $player->id, PDO::PARAM_INT)
		);
		return $this->selectPermissions($where, "a.tag");
	}
	
	//override
	public function selectPermissionsByAlliance(Alliance $alliance){
		$where = array(
			array("allianceId", $alliance->id, PDO::PARAM_INT)
		);
		return $this->selectPermissions($where, "pl.name");
	}
	
	//override
	public function selectPermissionsByPlayerAndAlliance(Player $player, Alliance $alliance){
		$where = array(
			array("playerId", $player->id, PDO::PARAM_INT),
			array("allianceId", $alliance->id, PDO::PARAM_INT)
		);
		$ret = $this->selectPermissions($where);
		
		if (count($ret) == 0){
			return null;
		}
		return $ret[0];
	}
	
	//override
	public function insertPermission(Permission $permission){
		$sql = "
		INSERT INTO permissions
		( playerId,  allianceId,  perms, joinDate) VALUES
		(:playerId, :allianceId, :perms, Now())
		";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":playerId", $permission->player->id, PDO::PARAM_INT);
		$stmt->bindValue(":allianceId", $permission->alliance->id, PDO::PARAM_INT);
		$perms = 0;
		if ($permission->permSubmit){
			$perms = $perms | self::PERMS_SUBMIT;
		}
		if ($permission->permView){
			$perms = $perms | self::PERMS_VIEW;
		}
		if ($permission->permAdmin){
			$perms = $perms | self::PERMS_ADMIN;
		}
		$stmt->bindValue(":perms", $perms, PDO::PARAM_INT);
		$stmt->execute();
		
		//update object
		$permission->id = $this->db->lastInsertId();
		$permission->joinDate = new DateTime('now');
	}
	
	//override
	public function updatePermission(Permission $permission){
		$sql = "
		UPDATE permissions SET
		perms = :perms
		WHERE permissionId = :permissionId
		";
		$stmt = $this->db->prepare($sql);
		$perms = 0;
		if ($permission->permSubmit){
			$perms = $perms | self::PERMS_SUBMIT;
		}
		if ($permission->permView){
			$perms = $perms | self::PERMS_VIEW;
		}
		if ($permission->permAdmin){
			$perms = $perms | self::PERMS_ADMIN;
		}
		$stmt->bindValue(":perms", $perms, PDO::PARAM_INT);
		$stmt->bindValue(":permissionId", $permission->id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	//override
	public function deletePermission($id){
		$sql = "DELETE FROM permissions WHERE permissionId = :permissionId";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":permissionId", $id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	//override
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
	
	//override
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
	
	//override
	public function insertPresidentPermission(Player $president, Alliance $alliance){
		$permission = new Permission();
		$permission->player = $president;
		$permission->alliance = $alliance;
		$permission->permSubmit = true;
		$permission->permView = true;
		$permission->permAdmin = true;
		$this->insertPermission($permission);
		
		$sql = "UPDATE alliances SET registeredDate = Now() WHERE allianceId = :allianceId";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":allianceId", $alliance->id, PDO::PARAM_INT);
		$stmt->execute();
		
		//update object
		$alliance->registeredDate = new DateTime("now");
	}
	
	//override
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
	
	//override
	public function dropAllTables(){
		$this->beginTransaction();
		try{
			$tables = array("submitLogs", "fleets", "permissions", "joinRequests", "joinLogs", "alliances", "players", "games");
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
	 * Gets a list of join logs.
	 * @param string $column the name of the column to include in the "WHERE" clause
	 * @param string $value the value of the column in the "WHERE" clause
	 * @param integer $pdoType the PDO type of the column
	 * @param string $orderBy (optional) the ORDER BY clause
	 * @return array(JoinLog) the join logs
	 */
	private function selectJoinLogs($column, $value, $pdoType, $orderBy = null){
		$joinLogs = array();
		
		$sql = "
		SELECT j.*,
		a.*, a.name AS allianceName,
		p.*, p.playerId AS thePlayerId, p.name AS playerName,
		p2.*, p2.playerId AS presidentId, p2.name AS presidentName, p2.lastLoginDate AS presidentLastLoginDate,
		g.*, g.name AS gameName, g.description AS gameDescription
		FROM joinLogs j
		INNER JOIN alliances a ON j.allianceID = a.allianceID
		INNER JOIN players p ON j.playerId = p.playerId
		INNER JOIN players p2 ON a.president = p2.playerId
		INNER JOIN games g ON p.gameId = g.gameId
		WHERE j.$column = :$column
		";
		if ($orderBy !== null){
			$sql .= " ORDER BY $orderBy";
		}
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":$column", $value, $pdoType);
		$stmt->execute();
		while ($row = $stmt->fetch()){
			$joinLog = new JoinLog();
			$joinLog->id = $row['joinLogId'];
			$joinLog->event = $row['event'];
			$joinLog->eventDate = $this->date($row['eventDate']);
			
			$game = new Game();
			$game->id = $row['gameId'];
			$game->name = $row['gameName'];
			$game->description = $row['gameDescription'];
			
			$player = new Player();
			$player->id = $row['thePlayerId'];
			$player->name = $row['playerName'];
			$player->lastLoginDate = $this->date($row['lastLoginDate']);
			$player->game = $game;
			$joinLog->player = $player;
			
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
			$joinLog->alliance = $alliance;
			
			$joinLogs[] = $joinLog;
		}
		
		return $joinLogs;
	}
	
	//override
	public function selectJoinLogsByPlayer(Player $player){
		return $this->selectJoinLogs("playerId", $player->id, PDO::PARAM_INT, "j.eventDate DESC");
	}

	//override
	public function insertJoinLog(Player $player, Alliance $alliance, $event){
		$sql = "
		INSERT INTO joinLogs
		( playerId,  allianceId,  event, eventDate) VALUES
		(:playerId, :allianceId, :event, Now())
		";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":playerId", $player->id, PDO::PARAM_INT);
		$stmt->bindValue(":allianceId", $alliance->id, PDO::PARAM_INT);
		$stmt->bindValue(":event", $event, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	//override
	public function deleteFleetsByPlayer(Player $player){
		$sql = "DELETE FROM fleets WHERE playerId = :playerId";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":playerId", $player->id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	//override
	public function insertFleet(Fleet $fleet){
		$sql = "INSERT INTO fleets
		( playerId, submitDate,  azterkScouts,  azterkBombers,  azterkDestroyers,  azterkCruisers,  azterkArmies,  humanScouts,  humanBombers,  humanDestroyers,  humanCruisers,  humanArmies, 	xillorScouts,  xillorBombers,  xillorDestroyers,  xillorCruisers, 	xillorArmies) VALUES
		(:playerId, Now(),       :azterkScouts, :azterkBombers, :azterkDestroyers, :azterkCruisers, :azterkArmies, :humanScouts, :humanBombers, :humanDestroyers, :humanCruisers, :humanArmies,	:xillorScouts, :xillorBombers, :xillorDestroyers, :xillorCruisers,	:xillorArmies)
		";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":playerId", $fleet->player->id, PDO::PARAM_INT);
		$stmt->bindValue(":azterkScouts", $fleet->azterkScouts, PDO::PARAM_INT);
		$stmt->bindValue(":azterkBombers", $fleet->azterkBombers, PDO::PARAM_INT);
		$stmt->bindValue(":azterkDestroyers", $fleet->azterkDestroyers, PDO::PARAM_INT);
		$stmt->bindValue(":azterkCruisers", $fleet->azterkCruisers, PDO::PARAM_INT);
		$stmt->bindValue(":azterkArmies", $fleet->azterkArmies, PDO::PARAM_INT);
		$stmt->bindValue(":humanScouts", $fleet->humanScouts, PDO::PARAM_INT);
		$stmt->bindValue(":humanBombers", $fleet->humanBombers, PDO::PARAM_INT);
		$stmt->bindValue(":humanDestroyers", $fleet->humanDestroyers, PDO::PARAM_INT);
		$stmt->bindValue(":humanCruisers", $fleet->humanCruisers, PDO::PARAM_INT);
		$stmt->bindValue(":humanArmies", $fleet->humanArmies, PDO::PARAM_INT);
		$stmt->bindValue(":xillorScouts", $fleet->xillorScouts, PDO::PARAM_INT);
		$stmt->bindValue(":xillorBombers", $fleet->xillorBombers, PDO::PARAM_INT);
		$stmt->bindValue(":xillorDestroyers", $fleet->xillorDestroyers, PDO::PARAM_INT);
		$stmt->bindValue(":xillorCruisers", $fleet->xillorCruisers, PDO::PARAM_INT);
		$stmt->bindValue(":xillorArmies", $fleet->xillorArmies, PDO::PARAM_INT);
		$stmt->execute();
		
		$fleet->id = $this->db->lastInsertId();
		$fleet->submitDate = new DateTime("now");
	}
	
	//override
	public function insertSubmitLog(Player $player){
		$sql = "INSERT INTO submitLogs
		( playerId, submitDate) VALUES
		(:playerId, Now())
		";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":playerId", $player->id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	//override
	public function selectLastPlayerSubmitLog(Player $player){
		$sql = "SELECT * FROM submitLogs WHERE playerId = :playerId ORDER BY submitDate DESC LIMIT 1";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":playerId", $player->id, PDO::PARAM_INT);
		$stmt->execute();
		if ($row = $stmt->fetch()){
			$s = new SubmitLog();
			$s->id = $row['submitLogId'];
			$s->player = $player; //TODO lazy
			$s->submitDate = $this->date($row['submitDate']);
			return $s;
		}
		return null;
	}
	
	//override
	public function beginTransaction(){
		$this->db->beginTransaction();
	}
	
	//override
	public function commit(){
		$this->db->commit();
	}
	
	//override
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
