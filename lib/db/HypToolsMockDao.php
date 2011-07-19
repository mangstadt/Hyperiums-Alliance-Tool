<?php
namespace db;

use \DateTime;

/**
 * Data access object that generates fake data for testing the UI.&nbsp
 * Data is persisted to the session and lost when the session ends.
 * @author mangstadt
 */
class HypToolsMockDao implements HypToolsDao{
	private $games;
	private $gamesNextId;
	
	private $alliances;
	private $alliancesNextId;
	
	private $players;
	private $playersNextId;
	
	private $permissions;
	private $permissionsNextId;
	
	private $joinRequests;
	private $joinRequestsNextId;
	
	private $joinLogs;
	private $joinLogsNextId;
	
	private $fleets;
	private $fleetsNextId;
	
	private $submitLogs;
	private $submitLogsNextId;
	
	/**
	 * The game that the player is logged into.
	 * @var Game
	 */
	private $game;

	/**
	 * Creates the DAO.
	 * @param string|Game $playerOrGame either the player's name (for when the player logs in) or the current game (for after the player logs in)
	 */
	public function __construct($playerOrGame){
		@session_start(); //start session if not already started
		
		if ($playerOrGame instanceof Game){
			$this->game = $playerOrGame;
		}
		
		if (isset($_SESSION['mock_games'])){
			//retrieve data from session
			
			$this->games = $_SESSION['mock_games'];
			$this->gamesNextId = $_SESSION['mock_gamesNextId'];
			$this->players = $_SESSION['mock_players'];
			$this->playersNextId = $_SESSION['mock_playersNextId'];
			$this->alliances = $_SESSION['mock_alliances'];
			$this->alliancesNextId = $_SESSION['mock_alliancesNextId'];
			$this->permissions = $_SESSION['mock_permissions'];
			$this->permissionsNextId = $_SESSION['mock_permissionsNextId'];
			$this->joinRequests = $_SESSION['mock_joinRequests'];
			$this->joinRequestsNextId = $_SESSION['mock_joinRequestsNextId'];
			$this->joinLogs = $_SESSION['mock_joinLogs'];
			$this->joinLogsNextId = $_SESSION['mock_joinLogsNextId'];
			$this->fleets = $_SESSION['mock_fleets'];
			$this->fleetsNextId = $_SESSION['mock_fleetsNextId'];
			$this->submitLogs = $_SESSION['mock_submitLogs'];
			$this->submitLogsNextId = $_SESSION['mock_submitLogsNextId'];
		} else {
			//session just started, create initial data
			
			$this->init();
			
			$game = new Game();
			$game->id = $this->gamesNextId++;
			$game->name = "Hyperiums6";
			$game->description = "Main game";
			$this->games[] = $game;
			
			//player object for the current user
			$me = new Player();
			$me->id = $this->playersNextId++;
			$me->game = $game;
			$me->name = is_string($playerOrGame) ? $playerOrGame : "mangst";
			$this->players[] = $me;
			
			//player bob
			$bob = new Player();
			$bob->id = $this->playersNextId++;
			$bob->game = $game;
			$bob->name = "Bob";
			$this->players[] = $bob;
			
			//player Cool_Dude67
			$coolDude67 = new Player();
			$coolDude67->id = $this->playersNextId++;
			$coolDude67->game = $game;
			$coolDude67->name = "Cool_Dude67";
			$this->players[] = $coolDude67;
			
			//player DaGod
			$daGod = new Player();
			$daGod->id = $this->playersNextId++;
			$daGod->game = $game;
			$daGod->name = "DaGod";
			$this->players[] = $daGod;
			
			//user is the president of [Pro-T]
			$proT = new Alliance();
			$proT->id = $this->alliancesNextId++;
			$proT->game = $game;
			$proT->name = "Pro14 recruitment alliance";
			$proT->tag = "Pro-T";
			$proT->registeredDate = new DateTime("2011-06-30 12:34:56");
			$proT->president = $me;
			$this->alliances[] = $proT;

			//[Noob] alliance
			$noob = new Alliance();
			$noob->id = $this->alliancesNextId++;
			$noob->game = $game;
			$noob->name = "We Pwn the Noobs";
			$noob->tag = "Noob";
			$noob->registeredDate = new DateTime("2011-06-30 12:34:56");
			$noob->president = $bob;
			$this->alliances[] = $noob;

			//[Xcel] alliance
			$xcel = new Alliance();
			$xcel->id = $this->alliancesNextId++;
			$xcel->game = $game;
			$xcel->name = "We eXcel at combat";
			$xcel->tag = "Xcel";
			$xcel->registeredDate = new DateTime("2011-05-22 08:21:43");
			$xcel->president = $daGod;
			$this->alliances[] = $xcel;

			//[Super] alliance
			$super = new Alliance();
			$super->id = $this->alliancesNextId++;
			$super->game = $game;
			$super->name = "Super alliance";
			$super->tag = "Super";
			$super->registeredDate = new DateTime("2011-05-22 08:21:43");
			$super->president = $daGod;
			$this->alliances[] = $super;

			//user is the president of [Pro-T]
			$p = new Permission();
			$p->id = $this->permissionsNextId++;
			$p->alliance = $proT;
			$p->player = $me;
			$p->permSubmit = true;
			$p->permView = true;
			$p->permAdmin = true;
			$p->joinDate = $proT->registeredDate;
			$this->permissions[] = $p;
			$j = new JoinLog();
			$j->id = $this->joinLogsNextId++;
			$j->alliance = $proT;
			$j->player = $me;
			$j->eventDate = $proT->registeredDate;
			$j->event = JoinLog::EVENT_ACCEPTED;
			$this->joinLogs[] = $j;
			
			//user is a member of [Noob] with full access
			$p = new Permission();
			$p->id = $this->permissionsNextId++;
			$p->alliance = $noob;
			$p->player = $me;
			$p->permSubmit = true;
			$p->permView = true;
			$p->permAdmin = true;
			$p->joinDate = new DateTime("2011-07-03 23:21:33");
			$this->permissions[] = $p;
			$j = new JoinLog();
			$j->id = $this->joinLogsNextId++;
			$j->alliance = $noob;
			$j->player = $me;
			$j->eventDate = $p->joinDate;
			$j->event = JoinLog::EVENT_ACCEPTED;
			$this->joinLogs[] = $j;
			
			//user is a member of [Xcel] with limited access
			$p = new Permission();
			$p->id = $this->permissionsNextId++;
			$p->alliance = $xcel;
			$p->player = $me;
			$p->permSubmit = true;
			$p->permView = false;
			$p->permAdmin = false;
			$p->joinDate = new DateTime("2011-07-04 03:23:11");
			$this->permissions[] = $p;
			$j = new JoinLog();
			$j->id = $this->joinLogsNextId++;
			$j->alliance = $xcel;
			$j->player = $me;
			$j->eventDate = $p->joinDate;
			$j->event = JoinLog::EVENT_ACCEPTED;
			$this->joinLogs[] = $j;
			
			//player Cool_Dude67 is a member of [Pro-T] with limited access
			$p = new Permission();
			$p->id = $this->permissionsNextId++;
			$p->alliance = $proT;
			$p->player = $coolDude67;
			$p->permSubmit = true;
			$p->permView = true;
			$p->permAdmin = false;
			$p->joinDate = new DateTime("2011-07-02 15:56:33");
			$this->permissions[] = $p;
			$j = new JoinLog();
			$j->id = $this->joinLogsNextId++;
			$j->alliance = $xcel;
			$j->player = $coolDude67;
			$j->eventDate = $p->joinDate;
			$j->event = JoinLog::EVENT_ACCEPTED;
			$this->joinLogs[] = $j;
			
			//user submitted a join request for [Super]
			$jr = new JoinRequest();
			$jr->id = $this->joinRequestsNextId++;
			$jr->player = $me;
			$jr->alliance = $super;
			$jr->requestDate = new DateTime("2011-06-22 09:21:56");
			$this->joinRequests[] = $jr;
			$j = new JoinLog();
			$j->id = $this->joinLogsNextId++;
			$j->alliance = $super;
			$j->player = $me;
			$j->eventDate = $jr->requestDate;
			$j->event = JoinLog::EVENT_REQUESTED;
			$this->joinLogs[] = $j;
			
			//player "bob" submitted a join request to [Pro-T]
			$jr = new JoinRequest();
			$jr->id = $this->joinRequestsNextId++;
			$jr->player = $bob;
			$jr->alliance = $proT;
			$jr->requestDate = new DateTime("2011-06-20 13:34:22");
			$this->joinRequests[] = $jr;
			$j = new JoinLog();
			$j->id = $this->joinLogsNextId++;
			$j->alliance = $proT;
			$j->player = $bob;
			$j->eventDate = $jr->requestDate;
			$j->event = JoinLog::EVENT_REQUESTED;
			$this->joinLogs[] = $j;
			
			//player "DaGod" submitted a join request to [Pro-T]
			$jr = new JoinRequest();
			$jr->id = $this->joinRequestsNextId++;
			$jr->player = $daGod;
			$jr->alliance = $proT;
			$jr->requestDate = new DateTime("2011-06-20 15:12:10");
			$this->joinRequests[] = $jr;
			$j = new JoinLog();
			$j->id = $this->joinLogsNextId++;
			$j->alliance = $proT;
			$j->player = $daGod;
			$j->eventDate = $jr->requestDate;
			$j->event = JoinLog::EVENT_REQUESTED;
			$this->joinLogs[] = $j;
		}
	}
	
	public function __destruct(){
		//persist data to session
		$_SESSION['mock_games'] = $this->games;
		$_SESSION['mock_gamesNextId'] = $this->gamesNextId;
		$_SESSION['mock_players'] = $this->players;
		$_SESSION['mock_playersNextId'] = $this->gamesNextId;
		$_SESSION['mock_alliances'] = $this->alliances;
		$_SESSION['mock_alliancesNextId'] = $this->gamesNextId;		
		$_SESSION['mock_permissions'] = $this->permissions;
		$_SESSION['mock_permissionsNextId'] = $this->permissionsNextId;
		$_SESSION['mock_joinRequests'] = $this->joinRequests;
		$_SESSION['mock_joinRequestsNextId'] = $this->joinRequestsNextId;
		$_SESSION['mock_joinLogs'] = $this->joinLogs;
		$_SESSION['mock_joinLogsNextId'] = $this->joinLogsNextId;
		$_SESSION['mock_fleets'] = $this->fleets;
		$_SESSION['mock_fleetsNextId'] = $this->fleetsNextId;
		$_SESSION['mock_submitLogs'] = $this->submitLogs;
		$_SESSION['mock_submitLogsNextId'] = $this->submitLogsNextId;
	}
	
	private function init(){
		$this->games = array();
		$this->gamesNextId = 1;
		
		$this->alliances = array();
		$this->alliancesNextId = 1;
		
		$this->players = array();
		$this->playersNextId = 1;
		
		$this->permissions = array();
		$this->permissionsNextId = 1;
		
		$this->joinRequests = array();
		$this->joinRequestsNextId = 1;
		
		$this->joinLogs = array();
		$this->joinLogsNextId = 1;
		
		$this->fleets = array();
		$this->fleetsNextId = 1;
		
		$this->submitLogs = array();
		$this->submitLogsNextId = 1;
	}

	//override
	public function setGame(Game $game){
		$this->game = $game;
	}

	//override
	public function upsertGame($name, $description){
		foreach ($this->games as $g){
			if (strcasecmp($g->name, $name) == 0){
				$g->name = $name;
				$g->description = $description;
				return $g;
			}
		}
		
		$g = new Game();
		$g->id = $this->gamesNextId++;
		$g->name = $name;
		$g->description = $description;
		$this->games[] = $g;
		return $g;
	}
	
	//override
	public function upsertPlayer($name, $hypPlayerId = null){
		//use player Id to find player
		if ($hypPlayerId != null){
			foreach ($this->players as $p){
				if ($p->game->id == $this->game->id && $hypPlayerId == $p->hypPlayerId){
					$p->name = $name;
					return $p;
				}
			}
		}
		
		//if player not found with hypPlayerId, use name
		foreach ($this->players as $p){
			if ($p->game->id == $this->game->id && strcasecmp($p->name, $name) == 0){
				$p->name = $name;
				return $p;
			}
		}
		
		//if player not found using name, then insert
		$p = new Player();
		$p->id = $this->playersNextId++;
		$p->name = $name;
		$p->hypPlayerId = $hypPlayerId;
		$p->game = $this->game;
		$this->players[] = $p;
		return $p;
	}
	
	//override
	public function updatePlayerLastLogin(Player $player){
		foreach ($this->players as $p){
			if ($p->id == $player->id){
				$now = new DateTime("now");
				$p->lastLoginDate = $now;
				$player->lastLoginDate = $now;
				break;
			}
		}
	}
	
	//override
	public function doesAllianceExist($tag){
		foreach ($this->alliances as $a){
			if ($a->game->id == $this->game->id && strcasecmp($tag, $a->tag) == 0){
				return true;
			}
		}
		return false;
	}
	
	//override
	public function upsertAlliance($tag, $name, $president){
		$presPlayer = $this->upsertPlayer($president);
		
		foreach ($this->alliances as $a){
			if ($a->game->id == $this->game->id && strcasecmp($tag, $a->tag) == 0){
				$a->tag = $tag;
				$a->name = $name;
				$a->president = $presPlayer;
			}
		}
		
		$a = new Alliance();
		$a->id = $this->alliancesNextId++;
		$a->tag = $tag;
		$a->name = $name;
		$a->president = $presPlayer;
		$a->game = $this->game;
		$this->alliances[] = $a;
	}

	//override
	public function hasPlayerMadeJoinRequest(Player $player, Alliance $alliance){
		foreach ($this->joinRequests as $j){
			if ($j->player->id == $player->id && $j->alliance->id == $alliance->id){
				return true;
			}
		}
		return false;
	}

	//override
	public function selectJoinRequestsByPlayer(Player $player){
		$js = array();
		foreach ($this->joinRequests as $j){
			if ($j->player->id == $player->id){
				$js[] = $j;
			}
		}
		usort($js, function($a, $b){
			//request date descending
			return $b->requestDate->getTimestamp() - $a->requestDate->getTimestamp();
		});
		return $js;
	}

	//override
	public function selectJoinRequestsByAlliance(Alliance $alliance){
		$js = array();
		foreach ($this->joinRequests as $j){
			if ($j->alliance->id == $alliance->id){
				$js[] = $j;
			}
		}
		usort($js, function($a, $b){
			//request date ascending
			return $a->requestDate->getTimestamp() - $b->requestDate->getTimestamp();
		});
		return $js;
	}
	
	//override
	public function selectJoinRequestById($id){
		foreach ($this->joinRequests as $j){
			if ($j->id == $id){
				return $j;
			}
		}
		return null;
	}

	//override
	public function deleteJoinRequest($id){
		foreach ($this->joinRequests as $i=>$j){
			if ($j->id == $id){
				unset($this->joinRequests[$i]);
				break;
			}
		}
	}
	
	//override
	public function deleteJoinRequestByPlayerAndAlliance(Player $player, Alliance $alliance){
		foreach ($this->joinRequests as $i=>$j){
			if ($j->player->id == $player->id && $j->alliance->id == $alliance->id){
				unset($this->joinRequests[$i]);
			}
		}
	}
	
	//override
	public function selectPermissionById($id){
		foreach ($this->permissions as $p){
			if ($p->id == $id){
				return $p;
			}
		}
		return null;
	}

	//override
	public function selectPermissionsByPlayer(Player $player){
		$ps = array();
		foreach ($this->permissions as $p){
			if ($p->player->id == $player->id){
				$ps[] = $p;
			}
		}
		usort($ps, function($a, $b){
			//tag ascending
			return strcmp($a->alliance->tag, $b->alliance->tag);
		});
		return $ps;
	}

	//override
	public function selectPermissionsByAlliance(Alliance $alliance){
		$ps = array();
		foreach ($this->permissions as $p){
			if ($p->alliance->id == $alliance->id){
				$ps[] = $p;
			}
		}
		usort($ps, function($a, $b){
			//player name ascending
			return strcmp($a->player->name, $b->player->name);
		});
		return $ps;
	}

	//override
	public function selectPermissionsByPlayerAndAlliance(Player $player, Alliance $alliance){
		foreach ($this->permissions as $p){
			if ($p->player->id == $player->id && $p->alliance->id == $alliance->id){
				return $p;
			}
		}
		return null;
	}

	//override
	public function insertPermission(Permission $permission){
		$permission->id = $this->permissionsNextId++;
		$permission->joinDate = new DateTime("now");
		$this->permissions[] = $permission;
	}
	
	//override
	public function updatePermission(Permission $permission){
		foreach ($this->permissions as $i=>$p){
			if ($p->id == $permission->id){
				$this->permissions[$i] = $permission;
				break;
			}
		}
	}

	//override
	public function deletePermission($id){
		foreach ($this->permissions as $i=>$p){
			if ($p->id == $id){
				unset($this->permissions[$i]);
				break;
			}
		}
	}

	//override
	public function doesPlayerBelongToAlliance(Player $player, Alliance $alliance){
		return $this->selectPermissionsByPlayerAndAlliance($player, $alliance) != null;
	}

	//override
	public function insertJoinRequest(Player $player, Alliance $alliance){
		$j = new JoinRequest();
		$j->id = $this->joinRequestsNextId++;
		$j->player = $player;
		$j->alliance = $alliance;
		$j->requestDate = new DateTime("now");
		$this->joinRequests[] = $j;
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
		
		foreach ($this->alliances as $a){
			if ($a->id == $alliance->id){
				$a->registeredDate = new DateTime("now");
				break;
			}
		}
		
		//update object
		$alliance->registeredDate = new DateTime("now");
	}

	//override
	public function selectAllianceByTag($tag){
		foreach ($this->alliances as $a){
			if ($a->game->id == $this->game->id && strcasecmp($a->tag, $tag) == 0){
				return $a;
			}
		}
		return null;
	}

	//override
	public function dropAllTables(){
		$this->init();
	}

	//override
	public function selectJoinLogsByPlayer(Player $player){
		$js = array();
		foreach ($this->joinLogs as $j){
			if ($j->player->id == $player->id){
				$js[] = $j;
			}
		}
		usort($js, function($a, $b){
			//event date descending
			return $b->eventDate->getTimestamp() - $a->eventDate->getTimestamp();
		});
		return $js;
	}

	//override
	public function insertJoinLog(Player $player, Alliance $alliance, $event){
		$j = new JoinLog;
		$j->id = $this->joinLogsNextId++;
		$j->player = $player;
		$j->alliance = $alliance;
		$j->event = $event;
		$j->eventDate = new DateTime("now");
		$this->joinLogs[] = $j;
	}
	
	//override
	public function deleteFleetsByPlayer(Player $player){
		foreach ($this->fleets as $i=>$fleet){
			if ($fleet->player->id == $player->id){
				unset($this->fleets[$i]);
			}
		}
	}
	
	//override
	public function insertFleet(Fleet $fleet){
		$fleet->id = $this->fleetsNextId++;
		$this->fleets[] = $fleet;
	}
	
	//override
	public function insertSubmitLog(Player $player){
		$s = new SubmitLog();
		$s->id = $this->submitLogsNextId++;
		$s->player = $player;
		$s->submitDate = new DateTime("now");
		$this->submitLogs[] = $s;
	}
	
	//override
	public function selectLastPlayerSubmitLog(Player $player){
		$cur = null;
		foreach ($this->submitLogs as $s){
			if ($s->player->id == $player->id){
				if ($cur == null || $s->submitDate->getTimestamp() > $cur->submitDate->getTimestamp()){
					$cur = $s;
				}
			}
		}
		return $cur;
	}

	//override
	public function beginTransaction(){
		//do nothing
	}
	
	//override
	public function commit(){
		//do nothing
	}
	
	//override
	public function rollBack(){
		//do nothing
	}
}
