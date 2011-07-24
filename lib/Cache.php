<?php
/**
 * Manages all cached resources.
 * @author mangstadt
 */
class Cache{
	/**
	 * Gets the games list
	 * @param function $funcRefresh the function to call if the cache needs to be refreshed. The function must return an array of \HAPI\Game objects.
	 * @return array(HAPI\Game) the list of games
	 */
	public static function getGamesList($funcRefresh){
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
			$games = $funcRefresh();
			file_put_contents($gamesCache, serialize($games));
		}
		
		return $games;
	}
}