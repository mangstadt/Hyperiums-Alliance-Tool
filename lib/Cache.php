<?php
use HAPI\HAPI;

/**
 * Manages all cached resources.
 * @author mangstadt
 */
class Cache{
	/**
	 * Gets the games list
	 * @return array(HAPI\Game) the list of games
	 */
	public static function getGamesList(){
		$gamesCache = Env::$cacheDir . '/games.ser';
		$games = null;
		if (file_exists($gamesCache)){
			if (time() - filemtime($gamesCache) < 60 * 60){
				//use cached file if it's less than one hour old
				$games = unserialize(file_get_contents($gamesCache));
			}
		}
		if ($games == null){
			//get list of games from Hyperiums servers
			$games = HAPI::getAllGames();
			//$games = HAPI::getAllGames();
			file_put_contents($gamesCache, serialize($games));
		}
		
		return $games;
	}
}