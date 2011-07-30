<?php
namespace hapidao;

use HAPI\Game;
use HAPI\FleetsInfo;
use HAPI\Fleet;
use HAPI\PlanetInfo;

/**
 * Generates mock HAPI webservice data.
 * @author mangstadt
 */
class HypToolsMockHapiDao implements HypToolsHapiDao{
	/**
	 * The current player's name.
	 * @var string
	 */
	private $playerName;
	
	/**
	 * Constructs a new mock DAO.
	 * @param string $playerName (optional) the current player's name
	 */
	public function __construct($playerName = null){
		$this->playerName = $playerName;
	}
	
	//override
	public function setPlayerIdentifier($playerName){
		$this->playerName = $playerName;
	}
	
	//override
	public function getGames(){
		$games = array();
		
		$game = new Game();
		$game->setName("MockGame");
		$game->setDescription("The game for mock mode.");
		$game->setState(Game::STATE_RUNNING_OPEN);
		$games[] = $game;
		
		return $games;
	}
	
	//override
	public function getFleetsInfo(){
		$fis = array();
		
		$nextId = 1;
		
		$fi = new FleetsInfo();
		$fs = array();
		for ($i = 0; $i < 3; $i++){
			$f = new Fleet();
			$f->setId($nextId++);
			$f->setOwner($this->playerName);
			$f->setRace(rand(0, 2));
			$f->setScouts(rand(500, 50000));
			$f->setBombers(rand(500, 5000));
			$f->setDestroyers(rand(500, 5000));
			$f->setCruisers(rand(100, 1000));
			$f->setCarriedArmies(rand(50, 500));
			$f->setGroundArmies(rand(50, 500));
			$f->setDefending(rand(0, 1));
			$f->setAutoDropping(false);
			$f->setBombing(false);
			$f->setCamouflaged(false);
			$f->setDelay(0);
			$f->setSellPrice(0);
			$fs[] = $f;
		}
		
		//add fleet that does not belong to the player
		$f = new Fleet();
		$f->setId($nextId++);
		$f->setOwner("DaGod");
		$f->setRace(rand(0, 2));
		$f->setScouts(5000000);
		$f->setBombers(5000000);
		$f->setDestroyers(5000000);
		$f->setCruisers(5000000);
		$f->setCarriedArmies(5000000);
		$f->setGroundArmies(5000000);
		$f->setDefending(rand(0, 1));
		$f->setAutoDropping(false);
		$f->setBombing(false);
		$f->setCamouflaged(false);
		$f->setDelay(0);
		$f->setSellPrice(0);
		$fs[] = $f;
		
		$fi->setFleets($fs);
		
		$fis[] = $fi;
		
		sleep(2);
		
		return $fis;
	}
	
	//override
	public function getPlanetInfo(){
		$pis = array();

		$pi = new PlanetInfo();
		$pi->setName("Mercury");
		$pi->setNumFactories(rand(10, 1000));
		$pis[] = $pi;
		
		$pi = new PlanetInfo();
		$pi->setName("Venus");
		$pi->setNumFactories(rand(10, 1000));
		$pis[] = $pi;
		
		$pi = new PlanetInfo();
		$pi->setName("Mars");
		$pi->setNumFactories(rand(10, 1000));
		$pis[] = $pi;
		
		return $pis;
	}
}