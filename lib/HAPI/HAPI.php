<?php
namespace HAPI;

/**
 * An interface for accessing the Hyperiums API (HAPI).&nbsp;
 * Compatable with HAPI v0.1.8.
 * @author mangst
 * @version 0.1.0
 */
class HAPI{
	/**
	 * The URL to the HAPI web service.
	 * @var string
	 */
	const URL = 'http://www.hyperiums.com/servlet/HAPI';
	
	/**
	 * The max number of requests per second you are allowed to send.
	 * @var integer
	 */
	const MAX_REQUESTS_PER_SEC = 3;
	
	/**
	 * The max number of requests per minute you are allowed to send.
	 * @var integer
	 */
	const MAX_REQUESTS_PER_MIN = 30;
	
	/**
	 * True to log all requests/responses, false not to.
	 * @var boolean
	 */
	private static $logMessages = false;

	/**
	 * The HAPI session.
	 * @var HAPISession
	 */
	private $session;
	
	/**
	 * Creates a new HAPI connection.
	 * @param string $gameName the game to connect to
	 * @param string $username the username
	 * @param string $hapiKey the external authentication key (login to Hyperiums and go to Preferences &gt; Authentication to generate one)
	 * @throws Exception if there was a problem authenticating or the authentication failed
	 */
	public function __construct($gameName, $username, $hapiKey){
		$this->session = $this->authenticate($gameName, $username, $hapiKey);
	}
	
	/**
	 * Authenticates the user so HAPI requests can be made.
	 * @param string $gameName the game to connect to
	 * @param string $username your username
	 * @param string $hapiKey the external authentication key (login to Hyperiums and go to Preferences &gt; Authentication to generate one)
	 * @throws Exception if there was a problem authenticating or the authentication failed
	 * @return HAPISession the HAPI session info
	 */
	protected function authenticate($gameName, $username, $hapiKey){
		$params = array(
			"game"=>$gameName,
			"player"=>$username,
			"hapikey"=>$hapiKey
		);
		$respParams = self::sendRequest(null, $params);

		$session = new HAPISession($respParams["gameid"], $respParams["playerid"], $respParams["authkey"], strtotime($respParams["servertime"]));
		return $session;
	}
	
	/**
	 * Gets a list of all games.
	 * @throws Exception if there was a problem making the request
	 * @return array(Game) all games
	 */
	public static function getAllGames(){
		$respParams = self::sendRequest("games");
		
		//parse the game information from the response
		$games = array();
		for ($i = 0; isset($respParams["game$i"]); $i++){
			$game = new Game();
			$game->setName($respParams["game$i"]);
			$game->setState($respParams["state$i"]);
			$game->setDescription($respParams["descr$i"]);
			$game->setLength($respParams["length$i"]);
			$game->setMaxEndDate($respParams["maxenddate$i"]); //this isn't a date
			$game->setPeec(self::boolean($respParams["ispeec$i"]));
			$game->setMaxPlanets($respParams["maxplanets$i"]);
			$game->setInitCash($respParams["initcash$i"]);
			$game->setMaxOfferedPlanets($respParams["maxofferedplanets$i"]);
			$game->setNextPlanetDelay($respParams["nextplanetdelay$i"]);
			$games[] = $game;
		}
		return $games;
	}
	
	/**
	 * Gets the HAPI session information.
	 * @return HAPISession the HAPI session information
	 */
	public function getSession(){
		return $this->session;
	}
	
	/**
	 * Gets information on all moving fleets.
	 * @throws Exception if there was a problem making the request
	 * @return array(MovingFleet)
	 */
	public function getMovingFleets(){
		$resp = $this->sendAuthRequest("getmovingfleets");
		
		$movingFleets = array();
		for ($i = 0; isset($resp["fleetid$i"]); $i++){
			$movingFleet = new MovingFleet();
			$movingFleet->setId($resp["fleetid$i"]);
			$movingFleet->setName($resp["fname$i"]);
			$movingFleet->setFrom($resp["from$i"]);
			$movingFleet->setTo($resp["to$i"]);
			$movingFleet->setDistance($resp["dist$i"]);
			$movingFleet->setDelay($resp["delay$i"]);
			$movingFleet->setDefending(self::boolean($resp["defend$i"]));
			$movingFleet->setAutoDropping(self::boolean($resp["autodrop$i"]));
			$movingFleet->setCamouflaged(self::boolean($resp["camouf$i"]));
			$movingFleet->setBombing(self::boolean($resp["bombing$i"]));
			$movingFleet->setRace($resp["race$i"]);
			$movingFleet->setBombers($resp["nbbomb$i"]);
			$movingFleet->setDestroyers($resp["nbdest$i"]);
			$movingFleet->setCruisers($resp["nbcrui$i"]);
			$movingFleet->setScouts($resp["nbscou$i"]);
			$movingFleet->setArmies($resp["nbarm$i"]);
			$movingFleets[] = $movingFleet;
		}
		return $movingFleets;
	}
	
	/**
	 * Gets all exploitation information from all your planets.
	 * @throws Exception if there was a problem making the request
	 * @return array(Exploitation)
	 */
	public function getExploitations(){
		$respParams = $this->sendAuthRequest("getexploitations");
		
		$exploitations = array();
		for ($i = 0; isset($respParams["planet$i"]); $i++){
			$exploitation = new Exploitation();
			$exploitation->setPlanetName($respParams["planet$i"]);
			$exploitation->setPlanetId($respParams["planetid$i"]);
			$exploitation->setNumExploits($respParams["nbexp$i"]);
			$exploitation->setNumInPipe($respParams["inpipe$i"]);
			$exploitation->setNumToBeDemolished($respParams["tobedem$i"]);
			$exploitation->setNumOnSale($respParams["nbonsale$i"]);
			$exploitation->setSellPrice($respParams["sellprice$i"]);
			$exploitation->setRentability($respParams["rentability$i"]);
			$exploitations[] = $exploitation;
		}
		return $exploitations;
	}
	
	/**
	 * Gets info on a specific planet or all of your planets.&nbsp;
	 * Includes general, trading, and infiltration info.
	 * @param $planetName (optional) the name of a specific planet to retrieve info on. This can be a planet you own or a planet that you have fleets/armies stationed on. If this is left out, it will return info on all of your planets.
	 * @throws Exception if a planet with the given name does not exist or it is not under the player's control or there was a problem sending the request
	 * @return PlanetInfo|array(PlanetInfo) returns an array if no planet name was specified
	 */
	public function getPlanetInfo($planetName = null){
		//TODO what does the response look like when it's a foreign planet that you have units on?
		$planetInfos = array();

		$params = array();
		$params["planet"] = ($planetName == null) ? "*" : $planetName;
		
		//get general info
		$params["data"] = "general";
		$respParams = $this->sendAuthRequest("getplanetinfo", $params);
		for ($i = 0; isset($respParams["planet$i"]); $i++){
			$planetInfo = new PlanetInfo();
			$planetInfo->setName($respParams["planet$i"]);
			$planetInfo->setX($respParams["x$i"]);
			$planetInfo->setY($respParams["y$i"]);
			$planetInfo->setSize($respParams["size$i"]);
			$planetInfo->setOrbit($respParams["orbit$i"]);
			$planetInfo->setGovernment($respParams["gov$i"]);
			$planetInfo->setProdType($respParams["ptype$i"]);
			$planetInfo->setTax($respParams["tax$i"]);
			$planetInfo->setNumExploits($respParams["exploits$i"]);
			$planetInfo->setNumExploitsInPipe($respParams["expinpipe$i"]);
			$planetInfo->setActivity($respParams["activity$i"]);
			$planetInfo->setPopulation($respParams["pop$i"]);
			$planetInfo->setRace($respParams["race$i"]);
			$planetInfo->setNrj($respParams["nrj$i"]);
			$planetInfo->setNrjMax($respParams["nrjmax$i"]);
			$planetInfo->setPurifying(self::boolean($respParams["purif$i"]));
			$planetInfo->setParanoidMode($respParams["parano$i"]);
			$planetInfo->setBlockaded(self::boolean($respParams["block$i"]));
			$planetInfo->setBlackHole(self::boolean($respParams["bhole$i"]));
			$planetInfo->setStasis(self::boolean($respParams["stasis$i"]));
			$planetInfo->setNexus(self::boolean($respParams["nexus$i"]));
			$planetInfo->setNexusBuildTimeLeft($respParams["nxbuild$i"]);
			$planetInfo->setNexusBuildTimeTotal($respParams["nxbtot$i"]);
			$planetInfo->setEcomark($respParams["ecomark$i"]);
			$planetInfo->setId($respParams["planetid$i"]);
			$planetInfo->setPublicTag($respParams["publictag$i"]);
			$planetInfo->setNumFactories($respParams["factories$i"]);
			$planetInfo->setCivLevel($respParams["civlevel$i"]);
			$planetInfo->setDefBonus($respParams["defbonus$i"]);
			$planetInfos[] = $planetInfo;
		}
		
		//no more than 3 requests are allowed per second, so wait before sending the next request to be safe
		sleep(1);

		//get trading info
		$params["data"] = "trading";
		$respParams = $this->sendAuthRequest("getplanetinfo", $params);
		for ($i = 0; isset($respParams["planet$i"]) && $i < count($planetInfos); $i++){
			$planetInfo = $planetInfos[$i];
			
			$trades = array();
			//note: parse_str() replaces dots in parameter names with underscores (example: "tid0.0" becomes "tid0_0")
			for ($j = 0; isset($respParams["tid{$i}_$j"]); $j++){
				$trade = new Trade();
				$trade->setId($respParams["tid{$i}_$j"]);
				$trade->setPlanetName($respParams["toplanet{$i}_$j"]);
				$trade->setPlanetTag($respParams["tag{$i}_$j"]);
				$trade->setPlanetDistance($respParams["dist{$i}_$j"]);
				$trade->setPlanetX($respParams["x{$i}_$j"]);
				$trade->setPlanetY($respParams["y{$i}_$j"]);
				$trade->setPlanetRace($respParams["race{$i}_$j"]);
				$trade->setPlanetActivity($respParams["activity{$i}_$j"]);
				$trade->setIncome($respParams["incomeBT{$i}_$j"]);
				$trade->setCapacity($respParams["capacity{$i}_$j"]);
				$trade->setTransportType($respParams["transtype{$i}_$j"]);
				$trade->setPending(self::boolean($respParams["ispending{$i}_$j"]));
				$trade->setAccepted(self::boolean($respParams["isaccepted{$i}_$j"]));
				$trade->setRequestor(self::boolean($respParams["isrequestor{$i}_$j"]));
				$trade->setUpkeep($respParams["upkeep{$i}_$j"]);
				$trade->setProdType($respParams["prodtype{$i}_$j"]);
				$trade->setPlanetBlockaded(self::boolean($respParams["isblockade{$i}_$j"]));
				$trades[] = $trade;
			}
			$planetInfo->setTrades($trades);
			$planetInfos[] = $planetInfo;
		}

		//no more than 3 requests are allowed per second, so wait before sending the next request to be safe
		sleep(1);
		
		//get infiltration info
		$params["data"] = "infiltr";
		$respParams = $this->sendAuthRequest("getplanetinfo", $params);
		for ($i = 0; isset($respParams["planet$i"]) && $i < count($planetInfos); $i++){
			$planetInfo = $planetInfos[$i];
			
			$infiltrations = array();
			//note: parse_str() replaces dots in parameter names with underscores (example: "tid0.0" becomes "tid0_0")
			for ($j = 0; isset($respParams["infid{$i}_$j"]); $j++){
				$infil = new Infiltration();
				$infil->setId($respParams["infid{$i}_$j"]);
				$infil->setPlanetName($respParams["planetname{$i}_$j"]);
				$infil->setPlanetTag($respParams["planettag{$i}_$j"]);
				$infil->setPlanetX($respParams["x{$i}_$j"]);
				$infil->setPlanetY($respParams["y{$i}_$j"]);
				$infil->setLevel($respParams["level{$i}_$j"]);
				$infil->setSecurity($respParams["security{$i}_$j"]);
				$infil->setGrowing(self::boolean($respParams["growing{$i}_$j"]));
				$infil->setCaptive(self::boolean($respParams["captive{$i}_$j"]));
				$infiltrations[] = $infil;
			}
			$planetInfo->setInfiltrations($infiltrations);
		}
		
		if ($planetName != null){
			return $planetInfos[0];
		}
		return $planetInfos;
	}
	
	/**
	 * Gets info on your fleets and armies that are stationed on a planet.&nbsp;
	 * Does not include fleets that are in transit (see getMovingFleets()).
	 * @param $planetName (optional) the name of a specific planet to retrieve fleet info on.
	 * This can be a planet you own or a planet that you have fleets/armies stationed on.
	 * If this is left out, it will return info on all of your planets and all planets that you have fleets/armies on.
	 * @throws Exception if there was a problem making the request
	 * @return array(FleetsInfo) an array of objects where each object represents planet that has 0 or more fleets
	 */
	public function getFleetsInfo($planetName = null){
		$fleetsInfos = array();
		
		$params = array();
		$params["planet"] = ($planetName == null) ? "*" : $planetName;
		
		//own planets
		$params["data"] = "own_planets";
		$resp = $this->sendAuthRequest("getfleetsinfo", $params);
		for ($i = 0; isset($resp["planet$i"]); $i++){
			$fleetsInfo = new FleetsInfo();
			$fleetsInfo->setOwnPlanet(true);
			$fleetsInfo->setPlanetName($resp["planet$i"]);
			$fleetsInfo->setStasis(self::boolean($resp["stasis$i"]));
			$fleetsInfo->setVacation(self::boolean($resp["vacation$i"]));
			$fleetsInfo->setNrj($resp["nrj$i"]);
			$fleetsInfo->setNrjMax($resp["nrjmax$i"]);
			$fleets = array();
			for ($j = 0; isset($resp["fleetid{$i}_$j"]); $j++){
				$fleet = new Fleet();
				$fleet->setId($resp["fleetid{$i}_$j"]);
				$fleet->setName($resp["fname{$i}_$j"]);
				$fleet->setSellPrice($resp["sellprice{$i}_$j"]);
				$fleet->setRace($resp["frace{$i}_$j"]);
				$fleet->setOwner($resp["owner{$i}_$j"]);
				$fleet->setDefending(self::boolean($resp["defend{$i}_$j"]));
				$fleet->setCamouflaged(self::boolean($resp["camouf{$i}_$j"]));
				$fleet->setBombing(self::boolean($resp["bombing{$i}_$j"]));
				$fleet->setAutoDropping(self::boolean($resp["autodrop{$i}_$j"]));
				$fleet->setDelay($resp["delay{$i}_$j"]);
				
				//note: army groups and fleet groups are separate, so if there are any ground armies in a fleet, there won't be any ships, and vice versa.
				
				$fleet->setGroundArmies(@$resp["garmies{$i}_$j"]);
				
				$fleet->setScouts(@$resp["scou{$i}_$j"]);
				$fleet->setCruisers(@$resp["crui{$i}_$j"]);
				$fleet->setBombers(@$resp["bomb{$i}_$j"]);
				$fleet->setDestroyers(@$resp["dest{$i}_$j"]);
				$fleet->setCarriedArmies(@$resp["carmies{$i}_$j"]);
				
				$fleets[] = $fleet;
			}
			$fleetsInfo->setFleets($fleets);
			$fleetsInfos[] = $fleetsInfo;
		}
		
		//foreign planets
		$params["data"] = "foreign_planets";
		$resp = $this->sendAuthRequest("getfleetsinfo", $params);
		for ($i = 0; isset($resp["planet$i"]); $i++){
			$fleetsInfo = new FleetsInfo();
			$fleetsInfo->setOwnPlanet(false);
			$fleetsInfo->setPlanetName($resp["planet$i"]);
			$fleetsInfo->setStasis(self::boolean($resp["stasis$i"]));
			$fleetsInfo->setVacation(self::boolean($resp["vacation$i"]));
			$fleets = array();
			for ($j = 0; isset($resp["fleetid{$i}_$j"]); $j++){
				$fleet = new Fleet();
				$fleet->setId($resp["fleetid{$i}_$j"]);
				$fleet->setName(@$resp["fname{$i}_$j"]);
				$fleet->setSellPrice($resp["sellprice{$i}_$j"]);
				$fleet->setRace($resp["frace{$i}_$j"]);
				$fleet->setOwner($resp["owner{$i}_$j"]);
				$fleet->setDefending(self::boolean($resp["defend{$i}_$j"]));
				$fleet->setCamouflaged(self::boolean($resp["camouf{$i}_$j"]));
				$fleet->setBombing(self::boolean(@$resp["bombing{$i}_$j"]));
				$fleet->setAutoDropping(self::boolean(@$resp["autodrop{$i}_$j"]));
				$fleet->setDelay(@$resp["delay{$i}_$j"]);
				
				//note: army groups and fleet groups are separate, so if there are any ground armies in a fleet, there won't be any ships, and vice versa.
				
				$fleet->setGroundArmies(@$resp["garmies{$i}_$j"]);
				
				$fleet->setScouts(@$resp["scou{$i}_$j"]);
				$fleet->setCruisers(@$resp["crui{$i}_$j"]);
				$fleet->setBombers(@$resp["bomb{$i}_$j"]);
				$fleet->setDestroyers(@$resp["dest{$i}_$j"]);
				$fleet->setCarriedArmies(@$resp["carmies{$i}_$j"]);
				
				$fleets[] = $fleet;
			}
			$fleetsInfo->setFleets($fleets);
			$fleetsInfos[] = $fleetsInfo;
		}
		
		return $fleetsInfos;
	}
	
	/**
	 * Gets a list of all planets that belong to an alliance.&nbsp;
	 * You must belong to the alliance.
	 * @param string $tag the alliance tag (without brakets)
	 * @param integer $start
	 * @throws Exception if there was a problem making the request
	 */
	public function getAlliancePlanets($tag, $start = 0){
		$params = array(
			"tag"=>$tag,
			"start"=>$start
		);
		$resp = $this->sendAuthRequest("getallianceplanets", $params);
		//TODO finish
	}
	
	/**
	 * Calls the "ismsg" method.
	 * @throws Exception if there was a problem making the request
	 * @return IsMsg the response
	 */
	public function isMsg(){
		$resp = $this->sendAuthRequest("ismsg");
		
		$isMsg = new IsMsg();
		$isMsg->setMsg(self::boolean($resp["ismsg"]));
		$isMsg->setReport(self::boolean($resp["isreport"]));
		return $isMsg;
	}
	
	/**
	 * Calls the "ismsginfo" method.
	 * @throws Exception if there was a problem making the request
	 * @teturn IsMsgInfo the response
	 */
	public function isMsgInfo(){
		$resp = $this->sendAuthRequest("ismsginfo");
		
		$isMsgInfo = new IsMsgInfo();
		$isMsgInfo->setMsg(self::boolean($resp["ismsg"]));
		$isMsgInfo->setPlanet(self::boolean($resp["isplanet"]));
		$isMsgInfo->setReport(self::boolean($resp["isreport"]));
		$isMsgInfo->setMilitary(self::boolean($resp["ismilitary"]));
		$isMsgInfo->setTrading(self::boolean($resp["istrading"]));
		$isMsgInfo->setInfiltration(self::boolean($resp["isinfiltr"]));
		$isMsgInfo->setControl(self::boolean($resp["iscontrol"]));
		return $isMsgInfo;
	}
	
	/**
	 * Gets all new player and planet messages.&nbsp;
	 * Note that these messages will be marked as "read" after this method is called.
	 * @throws Exception if there was a problem making the request
	 * @return array(Message) all new messages
	 */
	public function getNewMessages(){
		$messages = array();
		
		//this is confusing, see docs/example-responses.txt
		$resp = $this->sendAuthRequest("getnewmsg");
		$num = $resp["nbmsg"];
		if ($num > 0){
			$cur = 0;
			$curRecipient = null;
			$nextIndex = @$resp["planetstart$cur"];
			if ($nextIndex === null){
				$nextIndex = -1;
			}
			for ($i = 0; $i < $num; $i++){
				if ($i == $nextIndex){
					$curRecipient = $resp["planet$cur"];
					$cur++;
					$nextIndex = @$resp["planetstart$cur"];
				}
				
				$message = new Message();
				$message->setDate(strtotime($resp["date$i"]));
				$message->setType($resp["type$i"]);
				$message->setMessage($resp["msg$i"]);
				$message->setSubject($resp["subj$i"]);
				$sender = $resp["sender$i"];
				if ($sender == "null"){
					$sender = null;
				}
				$message->setSender($sender);
				$message->setRecipient($curRecipient);
				$messages[] = $message;
			}
		}
		return $messages;
	}
	
	/**
	 * Gets old player messages.
	 * @param \DateTime $startDate the start date
	 * @param integer $maxMessages the max number of messages to return
	 * @throws Exception if there was a problem making the request
	 * @return array(Message) the messages
	 */
	public function getOldPlayerMessages(\DateTime $startDate, $maxMessages){
		$messages = array();
		
		//TODO figure out how the start date works
		
		//$startDate->setTimezone(new \DateTimeZone("GMT")); //convert the start date to GMT
		$params = array(
			"startmsg"=>$startDate->format("Y-m-d G:i:s"),
			"maxmsg"=>$maxMessages
		);
		$resp = $this->sendAuthRequest("getoldpersomsg", $params);
		$num = $resp["nbmsg"];
		for ($i = 0; $i < $num; $i++){
			$message = new Message();
			$message->setDate(strtotime($resp["date$i"]));
			$message->setType(Message::TYPE_PERSONAL);
			$message->setMessage($resp["msg$i"]);
			$message->setSubject($resp["subj$i"]);
			$sender = $resp["sender$i"];
			if ($sender == "null"){
				$sender = null;
			}
			$message->setSender($sender);
			$messages[] = $message;
		}
		return $messages;
	}
	
	/**
	 * Gets old planet messages.
	 * @param \DateTime $startDate the start date
	 * @param integer $maxMessages the max number of messages to return
	 * @param string $planetName (optional) the planet you want to retrieve the messages of or null to get messages from all planets
	 * @throws Exception if there was a problem making the request
	 * @return array(Message) the messages
	 */
	public function getOldPlanetMessages(\DateTime $startDate, $maxMessages, $planetName = null){
		$messages = array();
		
		//TODO figure out how the start date works
		
		//$startDate->setTimezone(new \DateTimeZone("GMT")); //convert the start date to GMT
		$params = array(
			"startmsg"=>$startDate->format("Y-m-d G:i:s"),
			"maxmsg"=>$maxMessages,
			"planet"=>($planetName === null) ? "*" : $planetName
		);
		$resp = $this->sendAuthRequest("getoldplanetmsg", $params);
		$num = $resp["nbmsg"];
		for ($i = 0; $i < $num; $i++){
			$message = new Message();
			$message->setDate(strtotime($resp["date$i"]));
			$message->setType($resp["type$i"]);
			$message->setMessage($resp["msg$i"]);
			$message->setSubject($resp["subj$i"]);
			$sender = $resp["sender$i"];
			if ($sender == "null"){
				$sender = null;
			}
			$message->setSender($sender);
			$messages[] = $message;
		}
		return $messages;
	}
	
	/**
	 * Gets the version of HAPI.
	 * @throws Exception if there was a problem making the request
	 * @return string the version of HAPI
	 */
	public function getVersion(){
		$resp = $this->sendAuthRequest("version");
		return $resp["version"];
	}
	
	/**
	 * Logs you out of the current HAPI session.
	 * @throws Exception if there was a problem making the request
	 */
	public function logout(){
		$resp = $this->sendAuthRequest("logout");
		$status = $resp["status"];
		if ($status != "ok"){
			throw new \Exception("Logout failure.  Status code: $status");
		}
	}
	
	/**
	 * Gets info on a particular player.
	 * @param string $playerName (optional) the name of the player. If this is left out, then it will retrieve info on you.
	 * @throws Exception if there was a problem making the request
	 * @return PlayerInfo the info on the player
	 */
	public function getPlayerInfo($playerName = null){
		$params = array();
		if ($playerName != null){
			$params["targetplayer"] = $playerName;
		}
		$resp = $this->sendAuthRequest("getplayerinfo", $params);
		
		$playerInfo = new PlayerInfo();
		$playerInfo->setName($resp["name"]);
		$playerInfo->setHypRank($resp["hyprank"]);
		$playerInfo->setRankinf($resp["rankinf"]);
		$playerInfo->setScoreinf($resp["scoreinf"]);
		if ($playerName == null){
			//these parameters only appear if you are asking for information about yourself
			$playerInfo->setCash($resp["cash"]);
			$playerInfo->setRankfin($resp["rankfin"]);
			$playerInfo->setScorefin($resp["scorefin"]);
			$playerInfo->setRankpow($resp["rankpow"]);
			$playerInfo->setScorepow($resp["scorepow"]);
			$playerInfo->setPlanets($resp["nbplanets"]);
			$playerInfo->setLastIncome($resp["lastincome"]);
		}
		return $playerInfo;
	}
	
	/**
	 * Converts a boolean query string parameter to a boolean data type.
	 * @param string $value should be either "0" or "1"
	 * @return boolean the query string parameter converted to a boolean data type
	 */
	protected static function boolean($value){
		return $value === "1";
	}
	
	/**
	 * Sends a HAPI request.
	 * @param string $method the method to call
	 * @param array(string=>string) $params (optional) additional parameters to add to the request
	 * @throws Exception if there was a problem sending the request or an error response was returned
	 * @return array(string=>string) the response
	 */
	protected static function sendRequest($method, array $params = array()){
		//build request URL
		$params["request"] = $method;
		$url = self::URL . "?" . http_build_query($params);
		
		//make the request
		$response = file_get_contents($url);
		if (self::$logMessages){
			error_log("HAPI request: $method\n  url: $url\n  response: $response\n\n");
		}
		
		//problem sending request?
		if ($response === false){
			throw new \Exception("Problem sending the request.");
		}
		
		//ampersands are not URL encoded, so make them URL encoded so parse_str() doesn't break
		$response = str_replace("[:&:]", urlencode("&"), $response);
		
		//parse the query string into an assoc array
		parse_str($response, $respParams);
		
		//check for errors in the response
		$error = @$respParams["error"];
		if ($error !== null){
			throw new \Exception($error);
		}
		
		return $respParams;
	}
	
	/**
	 * Sends a HAPI request including auth info.
	 * @param string $method the method to call
	 * @param array(string=>string) $params (optional) additional parameters to add to the request
	 * @throws Exception if there was a problem sending the request or an error response was returned
	 * @return array(string=>string) the response
	 */
	protected function sendAuthRequest($method, array $params = array()){
		//add auth parameters
		$params["gameid"] = $this->session->getGameId();
		$params["playerid"] = $this->session->getPlayerId();
		$params["authkey"] = $this->session->getAuthKey();
		
		return self::sendRequest($method, $params);
	}
	
	/**
	 * Sets whether or not to log all requests/responses to the PHP error log.&nbsp;
	 * Defaults to false.
	 * @param boolean $logMessage true to log all requests/responses, false not to
	 */
	public static function setLogMessages($logMessages){
		self::$logMessages = $logMessages;
	}
}