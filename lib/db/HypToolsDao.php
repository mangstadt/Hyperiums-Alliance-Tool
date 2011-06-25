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
	 * The database connection
	 * @var PDO
	 */
	private $db;
	
	/**
	 * Creates the DAO.
	 */
	public function __construct(){
		if (isset($_SERVER['db_host'])){
			//we are on pagodabox
			$host = $_SERVER['db_host'];
			$name = $_SERVER['db_name'];
			$user = $_SERVER['db_user'];
			$pass = $_SERVER['db_pass'];
			$this->db = new PDO("mysqli:host=$host;dbName=$name", $user, $pass);
		} else {
			//we are on my local workstation
			//host of "localhost" binds to socket at /var/mysql/mysql.sock -- edit /etc/my.cf
			$this->db = new PDO("mysql:host=localhost", "root", "root");
			$this->db->exec("CREATE DATABASE IF NOT EXISTS hypTools");
			$this->db->exec("USE hypTools");
		}
		
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
	}
	
	/**
	 * Selects a player from the database or creates a new row if one doesn't exist.
	 * @param string $name the player name
	 * @return Player the player
	 */
	public function selsertPlayer($name){
		$sql = "SELECT * FROM players WHERE Ucase(name) = :name";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":name", strtoupper($name), PDO::PARAM_STR);
		$stmt->execute();
		if ($row = $stmt->fetch()){
			$player = new Player();
			$player->id = $row['playerId'];
			$player->name = $row['name'];
			if ($row['lastLoginDate'] != null){
				$player->lastLoginDate = new DateTime($row['lastLoginDate']);
			}
			$player->lastLoginIP = $row['lastLoginIP'];
		} else {
			$sql = "
			INSERT INTO players
			( name, lastLoginDate, lastLoginIP) VALUES
			(:name, NULL,          NULL)
			";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(":name", $name, PDO::PARAM_STR);
			$stmt->execute();
			
			$player = new Player();
			$player->id = $this->db->lastInsertId();
			$player->name = $name;
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
		lastLoginDate = Now(),
		lastLoginIP = :lastLoginIP
		WHERE playerId = :playerId
		";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":playerId", $player->id, PDO::PARAM_INT);
		$stmt->bindValue(":lastLoginIP", $_SERVER["REMOTE_ADDR"], PDO::PARAM_STR);
		$stmt->execute();
		
		//update object
		$player->lastLoginDate = new DateTime("now");
		$player->lastLoginIP = $_SERVER["REMOTE_ADDR"];
	}
	
	/**
	 * Gets the player's permissions
	 * @param Player $player the player
	 * @param integer $status (optional) only return permissions of this status
	 * @param string $orderBy (optional) sort the permissions by this column
	 * @return Permission the player's permissions
	 */
	private function selectPermissions($player, $status = null, $orderBy = null){
		$sql = "
		SELECT p.*, a.*, pl.*, a.name AS allianceName, p2.playerId AS presidentId, p2.name AS presidentName, pl.name AS playerName
		FROM permissions p
		INNER JOIN players pl ON p.playerId = pl.playerId
		INNER JOIN alliances a ON p.allianceId = a.allianceId
		INNER JOIN players p2 ON a.president = p2.playerId
		WHERE p.playerId = :playerId
		";
		if ($status !== null){
			$sql .= " AND p.status = :status";
		}
		if ($orderBy !== null){
			$sql .= " ORDER BY $orderBy";
		}
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":playerId", $player->id, PDO::PARAM_INT);
		if ($status !== null){
			$stmt->bindValue(":status", $status, PDO::PARAM_INT);
		}
		$stmt->execute();
		$permissions = array();
		while ($row = $stmt->fetch()){
			$permission = new Permission();
			$permission->id = $row['permissionId'];
			$permission->status = $row['status'];
			$permission->requestDate = new DateTime($row['requestDate']);
			$acceptDate = $row['acceptDate'];
			if ($acceptDate != null){
				$permission->acceptDate = new DateTime($acceptDate);
			}
			$permission->permSubmit = $this->bool($row['permSubmit']);
			$permission->permView = $this->bool($row['permView']);
			$permission->permAdmin = $this->bool($row['permAdmin']);
			
			$player = new Player();
			$player->id = $row['playerId'];
			$player->name = $row['playerName'];
			$permission->player = $player;
			
			$alliance = new Alliance();
			$alliance->id = $row['allianceId'];
			$alliance->tag = $row['tag'];
			$alliance->name = $row['allianceName'];
			$permission->alliance = $alliance;
			
			$president = new Player();
			$president->id = $row["presidentId"];
			$president->name = $row["presidentName"];
			$permission->president = $president;
			
			$permissions[] = $permission;
		}
		return $permissions;
	}
	
	/**
	 * Gets all permissions of the alliances that the player has been accepted into.
	 * @param $player the player
	 * @return array(Permission) the permissions
	 */
	public function selectAcceptedPermissions($player){
		return $this->selectPermissions($player, Permission::STATUS_ACCEPTED, "a.tag");
	}
	
	/**
	 * Gets the permissions of all pending alliance requests.
	 * @param $player the player
	 * @return array(Permission) the permissions
	 */
	public function selectPendingPermissions($player){
		return $this->selectPermissions($player, Permission::STATUS_REQUESTED, "p.requestDate DESC");
	}
	
	/**
	 * Determines whether an alliance exists or not.
	 * @param string $tag the alliance tag
	 * @return boolean true if the alliance exists, false if not
	 */
	public function doesAllianceExist($tag){
		$sql = "SELECT Count(*) FROM alliances WHERE Ucase(tag) = :tag";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":tag", strtoupper($tag), PDO::PARAM_STR);
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
		$presPlayer = $this->selsertPlayer($president);
		
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
			( tag,  name,  president, registeredDate,  motd) VALUES
			(:tag, :name, :president, NULL,            NULL)
			";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(":tag", $tag, PDO::PARAM_STR);
			$stmt->bindValue(":name", $name, PDO::PARAM_STR);
			$stmt->bindValue(":president", $presPlayer->id, PDO::PARAM_INT);
			$stmt->execute();
		}
	}
	
	/**
	 * Determines if a player has either (1) requested to join an alliance or (2) has already been accepted into an alliance.
	 * @param Player $player the player
	 * @param Alliance $alliance the alliance
	 * @return boolean true if the player has requested or is part of the alliance, false if not
	 */
	public function selectAlreadyRequested(Player $player, Alliance $alliance){
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
	public function insertAllianceJoinRequest(Player $player, Alliance $alliance){
		$sql = "
		INSERT INTO permissions
		( playerId,  allianceId,  status, requestDate, permSubmit, permView, permAdmin) VALUES
		(:playerId, :allianceId, :status, Now(),       0,          0,        0)
		";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":playerId", $player->id, PDO::PARAM_INT);
		$stmt->bindValue(":allianceId", $alliance->id, PDO::PARAM_INT);
		$stmt->bindValue(":status", Permission::STATUS_REQUESTED, PDO::PARAM_INT);
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
		( playerId,  allianceId,  status, requestDate, acceptDate, permSubmit, permView, permAdmin) VALUES
		(:playerId, :allianceId, :status, Now(),       Now(),      1,          1,        1)
		";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":playerId", $president->id, PDO::PARAM_INT);
		$stmt->bindValue(":allianceId", $alliance->id, PDO::PARAM_INT);
		$stmt->bindValue(":status", Permission::STATUS_ACCEPTED, PDO::PARAM_INT);
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
	public function selectAlliance($tag){
		$alliance = null;
		
		$sql = "
		SELECT a.*, p.*, p.name AS playerName
		FROM alliances a INNER JOIN players p ON a.president = p.playerId
		WHERE Ucase(a.tag) = :tag
		";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":tag", strtoupper($tag), PDO::PARAM_STR);
		$stmt->execute();
		if ($row = $stmt->fetch()){
			$alliance = new Alliance();
			$alliance->id = $row['allianceId'];
			$alliance->tag = $row['tag'];
			$alliance->name = $row['name'];
			if ($row['registeredDate'] != null){
				$alliance->registeredDate = new DateTime($row['registeredDate']);
			}
			$alliance->motd = $row['motd'];
			
			$president = new Player();
			$president->id = $row["playerId"];
			$president->name = $row["playerName"];
			$date = $row["lastLoginDate"];
			if ($date != null){
				$president->lastLoginDate = new DateTime($date);
			}
			$president->lastLoginIP = $row["lastLoginIP"];
			$alliance->president = $president;
		}
		
		return $alliance;
	}
	
	/**
	 * Gets an alliance's ID.
	 * @param string $tag the alliance tag
	 * @return integer the alliance's ID or null if not found
	 */
	private function selectAllianceId($tag){
		$sql = "SELECT allianceId FROM alliances WHERE Ucase(tag) = :tag";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":tag", strtoupper($tag), PDO::PARAM_STR);
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
}