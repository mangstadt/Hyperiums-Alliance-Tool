<?php
session_start();

require_once '../lib/bootstrap.php';
use HAPI\Parsers\AllianceParser;
use hapidao\HypToolsRealHapiDao;
use db\HypToolsMySqlDao;
use db\Permission;

//handle AJAX requests
$method = @$_REQUEST['method'];
if ($method != null){
	$response = null;
	if (!isset($_SESSION['loggedIn'])){
		$response['errors'][] = 'Not logged in.';
		$response['status'] = 400;
	} else {
		if ($method == 'updateAlliances'){
			$response = doUpdateAlliances();
		} else if ($method == 'makePresident'){
			$response = doMakePresident($_POST['game'], $_POST['player'], $_POST['tag']);
		} else if ($method == 'makeMember'){
			$response = doMakeMember($_POST['game'], $_POST['player'], $_POST['tag'], $_POST['permSubmit'] == 'true', $_POST['permView'] == 'true', $_POST['permAdmin'] == 'true');
		} else if ($method == 'wipe'){
			$response = doWipe();
		}
	
		if ($response === null){
			//unknown method name
			$response['errors'][] = "Unknown method \"$method\".";
			$response['status'] = 400;
		}
	}
	
	send($response);
}

if (!isset($_SESSION['loggedIn'])){
	$password = @$_POST["password"];
	if ($password !== null){
		$correctPw = isset($_SERVER['admin_pw']) ? $_SERVER['admin_pw'] : 'glass';
		if ($password == $correctPw){
			$_SESSION['loggedIn'] = true;
		} else {
			$wrongPassword = true;
		}
	}
}

$loggedOut = false;
if (isset($_GET['logout'])){
	$loggedOut = true;
	session_destroy();
	unset($_SESSION['loggedIn']);
}

$hapiDao = new HypToolsRealHapiDao();
$games = $hapiDao->getGames();

?>

<html>

<head>
<title>Admin</title>
<script src="../js/global.js"></script>
<script type="text/javascript">
	HTMLElement.prototype.setVisible = function(visible){
		this.style.visibility = visible ? "visible" : "hidden";
	};
	HTMLElement.prototype.setEnabled = function(enabled){
		this.disabled = !enabled;
	};

	/**
	 * Escapes a string for HTML.
	 * @return the escaped string
	 */
	String.prototype.escapeHTML = function () {                                        
		return(                                                                 
			this.replace(/&/g,'&amp;').                                         
			replace(/>/g,'&gt;').                                           
			replace(/</g,'&lt;').                                           
			replace(/"/g,'&quot;')                                         
		);                                                                
	};

	/**
	 * Updates the alliance data in the database.
	 */
	function updateAlliances(){
		var prefix = "updateAlliances";
		var button = $(prefix + "Button");
		var loading = $(prefix + "Loading");
		var message = $(prefix + "Message");

		message.setVisible(false);
		button.setEnabled(false);
		loading.setVisible(true);

		var xmlhttp = newXmlhttp();
		xmlhttp.onreadystatechange = function(){
			if (xmlhttp.readyState == 4){
				var msg = "";

				//parse response
				var contentType = xmlhttp.getResponseHeader('Content-type').toLowerCase();
				if (contentType == "application/json"){
					var response = eval('(' + xmlhttp.responseText + ')');

					if (xmlhttp.status == 200){
						if (response.errors){
							msg += '<font color="red">';
							for (var i = 0; i < response.errors.length; i++){
								var e = response.errors[i];
								msg += 'Error: ' + e.escapeHTML() + '<br />';
							}
							msg += '</font>';
						}

						if (response.warnings){
							msg += '<font color="orange">';
							for (var i = 0; i < response.warnings.length; i++){
								var w = response.warnings[i];
								msg += 'Warning: ' + w.escapeHTML() + '<br />';
							}
							msg += '</font>';
						}

						if (response.processed){
							for (var i = 0; i < response.processed.length; i++){
								var p = response.processed[i];
								msg += '<img src="img/checkmark-sml.png" /> Processed <b>' + p.count + '</b> alliances in file <b>' + p.file.escapeHTML() + '</b>.<br />';
							}
						}
						
					} else {
						if (response.errors){
							msg += '<font color="red">';
							for (var i = 0; i < response.errors.length; i++){
								var e = response.errors[i];
								msg += 'Error: ' + e.escapeHTML() + '<br />';
							}
							msg += '</font>';
						}
					}
				} else {
					msg = xmlhttp.responseText;
				}
				
				loading.setVisible(false);
				button.setEnabled(true);
				message.innerHTML = msg;
				message.setVisible(true);
			}
		};
		
		xmlhttp.open("POST","index.php?method=updateAlliances", true);
		xmlhttp.send();
	}

	/**
	 * Makes a player president of an alliance.
	 */
	function makePresident(){
		var prefix = "makePresident";
		var button = $(prefix + "Button");
		var loading = $(prefix + "Loading");
		var message = $(prefix + "Message");
		var game = $(prefix + "Game");
		var player = $(prefix + "Player");
		var tag = $(prefix + "Tag");

		message.setVisible(false);
		button.setEnabled(false);
		loading.setVisible(true);

		var xmlhttp = newXmlhttp();
		xmlhttp.onreadystatechange = function(){
			if (xmlhttp.readyState == 4){
				msg = "";

				//parse response
				var contentType = xmlhttp.getResponseHeader('Content-type').toLowerCase();
				if (contentType == "application/json"){
					var response = eval('(' + xmlhttp.responseText + ')');

					if (xmlhttp.status == 200){
						if (response.errors){
							msg += '<font color="red">';
							for (var i = 0; i < response.errors.length; i++){
								var e = response.errors[i];
								msg += 'Error: ' + e.escapeHTML() + '<br />';
							}
							msg += '</font>';
						}

						if (response.warnings){
							msg += '<font color="orange">';
							for (var i = 0; i < response.warnings.length; i++){
								var w = response.warnings[i];
								msg += 'Warning: ' + w.escapeHTML() + '<br />';
							}
							msg += '</font>';
						}

						if (response.tag){
							var player = response.player;
							var tag = response.tag;
							msg += '<img src="img/checkmark-sml.png" /> Player <b>' + player.escapeHTML() + '</b> is now the president of <b>[' + tag.escapeHTML() + ']</b>';
						}
					} else {
						if (response.errors){
							msg += '<font color="red">';
							for (var i = 0; i < response.errors.length; i++){
								var e = response.errors[i];
								msg += 'Error: ' + e.escapeHTML() + '<br />';
							}
							msg += '</font>';
						}
					}
				} else {
					msg = xmlhttp.responseText;
				}

				loading.setVisible(false);
				button.setEnabled(true);
				message.innerHTML = msg;
				message.setVisible(true);
			}
		};
		
		xmlhttp.open("POST","index.php?method=makePresident", true);
		xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		xmlhttp.send(queryString({
			'game': game.value,
			'player': player.value,
			'tag': tag.value
		}));
	}

	/**
	 * Makes a player a member of an alliance.
	 */
	function makeMember(){
		var prefix = "makeMember";
		var button = $(prefix + "Button");
		var loading = $(prefix + "Loading");
		var message = $(prefix + "Message");
		var game = $(prefix + "Game");
		var player = $(prefix + "Player");
		var tag = $(prefix + "Tag");
		var permSubmit = $(prefix + "Submit");
		var permView = $(prefix + "View");
		var permAdmin = $(prefix + "Admin");

		message.setVisible(false);
		button.setEnabled(false);
		loading.setVisible(true);

		var xmlhttp = newXmlhttp();
		xmlhttp.onreadystatechange = function(){
			if (xmlhttp.readyState == 4){
				msg = "";

				//parse response
				var contentType = xmlhttp.getResponseHeader('Content-type').toLowerCase();
				if (contentType == "application/json"){
					var response = eval('(' + xmlhttp.responseText + ')');

					if (xmlhttp.status == 200){
						if (response.errors){
							msg += '<font color="red">';
							for (var i = 0; i < response.errors.length; i++){
								var e = response.errors[i];
								msg += 'Error: ' + e.escapeHTML() + '<br />';
							}
							msg += '</font>';
						}

						if (response.warnings){
							msg += '<font color="orange">';
							for (var i = 0; i < response.warnings.length; i++){
								var w = response.warnings[i];
								msg += 'Warning: ' + w.escapeHTML() + '<br />';
							}
							msg += '</font>';
						}

						if (response.tag){
							var player = response.player;
							var tag = response.tag;
							msg += '<img src="img/checkmark-sml.png" /> ';
							if (response.inserted){
								msg += 'Player <b>' + player.escapeHTML() + '</b> is now a member of <b>[' + tag.escapeHTML() + ']</b>';
							} else {
								msg += 'Updated permissions for player <b>' + player.escapeHTML() + '</b> in alliance <b>[' + tag.escapeHTML() + ']</b>';
							}
						}
					} else {
						if (response.errors){
							msg += '<font color="red">';
							for (var i = 0; i < response.errors.length; i++){
								var e = response.errors[i];
								msg += 'Error: ' + e.escapeHTML() + '<br />';
							}
							msg += '</font>';
						}
					}
				} else {
					msg = xmlhttp.responseText;
				}

				loading.setVisible(false);
				button.setEnabled(true);
				message.innerHTML = msg;
				message.setVisible(true);
			}
		};
		
		xmlhttp.open("POST","index.php?method=makeMember", true);
		xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		xmlhttp.send(queryString({
			'game': game.value,
			'player': player.value,
			'tag': tag.value,
			'permSubmit': permSubmit.checked,
			'permView': permView.checked,
			'permAdmin': permAdmin.checked
		}));
	}

	/**
	 * Wipes the database.
	 */
	function wipe(){
		var prefix = "wipe";
		var button = $(prefix + "Button");
		var loading = $(prefix + "Loading");
		var message = $(prefix + "Message");

		message.setVisible(false);
		button.setEnabled(false);
		loading.setVisible(true);

		var xmlhttp = newXmlhttp();
		xmlhttp.onreadystatechange = function(){
			if (xmlhttp.readyState == 4){
				var msg = "";

				//parse response
				var contentType = xmlhttp.getResponseHeader('Content-type').toLowerCase();
				if (contentType == "application/json"){
					var response = eval('(' + xmlhttp.responseText + ')');

					if (xmlhttp.status == 200){
						if (response.errors){
							msg += '<font color="red">';
							for (var i = 0; i < response.errors.length; i++){
								var e = response.errors[i];
								msg += 'Error: ' + e.escapeHTML() + '<br />';
							}
							msg += '</font>';
						}

						if (response.warnings){
							msg += '<font color="orange">';
							for (var i = 0; i < response.warnings.length; i++){
								var w = response.warnings[i];
								msg += 'Warning: ' + w.escapeHTML() + '<br />';
							}
							msg += '</font>';
						}

						if (msg.length == 0){
							msg = '<img src="img/checkmark-sml.png" /> Database wiped.';
						}
					} else {
						if (response.errors){
							msg += '<font color="red">';
							for (var i = 0; i < response.errors.length; i++){
								var e = response.errors[i];
								msg += 'Error: ' + e.escapeHTML() + '<br />';
							}
							msg += '</font>';
						}
					}
				} else {
					msg = xmlhttp.responseText;
				}
				
				loading.setVisible(false);
				button.setEnabled(true);
				message.innerHTML = msg;
				message.setVisible(true);
			}
		};
		
		xmlhttp.open("POST","index.php?method=wipe", true);
		xmlhttp.send();
	}
</script>
</head>

<body>
	<?php
	if (isset($_SESSION['loggedIn'])):
		?>
		<div style="width:500px">
			<b>HypTools Admin</b> - <a href="index.php?logout">Logout</a><br />
			<br />
			
			<b>Update Alliances</b>
			<p>Update the alliance info in the DB from HAPI alliance data files located in this dir.</p>
			<div id="updateAlliancesMessage" style="visibility:hidden"></div>
			<div>
				<button onclick="updateAlliances()" id="updateAlliancesButton">Update alliances</button>
				<span id="updateAlliancesLoading" style="visibility:hidden">
					<img src="img/loading.gif" /> working...
				</span>
			</div>
			<hr />
			
			<b>Make President</b>
			<p>Make a player president of an alliance.</p>
			<p>
				Game: <select id="makePresidentGame">
				<?php
				foreach ($games as $game):
					$name = $game->getName();
					echo '<option value="' . htmlspecialchars($name) . '">' . htmlspecialchars($name) . '</option>' ;
				endforeach;
				?>
				</select><br />
				Player: <input type="text" id="makePresidentPlayer" onkeydown="if (event.keyCode == 13) makePresident()" /><br />
				Alliance Tag: <input type="text" id="makePresidentTag" onkeydown="if (event.keyCode == 13) makePresident()" /><br />
			</p>
			<div id="makePresidentMessage" style="visibility:hidden"></div>
			<div>
				<button onclick="makePresident()" id="makePresidentButton">Make President</button>
				<span id="makePresidentLoading" style="visibility:hidden">
					<img src="img/loading.gif" /> working...
				</span>
			</div>
			<hr />
			
			<b>Make Member</b>
			<p>Make a player a member of an alliance.</p>
			<p>
				Game: <select id="makeMemberGame">
				<?php
				foreach ($games as $game):
					$name = $game->getName();
					echo '<option value="' . htmlspecialchars($name) . '">' . htmlspecialchars($name) . '</option>' ;
				endforeach;
				?>
				</select><br />
				Player: <input type="text" id="makeMemberPlayer" onkeydown="if (event.keyCode == 13) makeMember()" /><br />
				Alliance Tag: <input type="text" id="makeMemberTag" onkeydown="if (event.keyCode == 13) makeMember()" /><br />
				Permissions: <input type="checkbox" id="makeMemberSubmit" disabled="disabled" checked="checked" /> Submit | <input type="checkbox" id="makeMemberView" checked="checked" /> View | <input type="checkbox" id="makeMemberAdmin" checked="checked" /> Admin  
			</p>
			<div id="makeMemberMessage" style="visibility:hidden"></div>
			<div>
				<button onclick="makeMember()" id="makeMemberButton">Make Member</button>
				<span id="makeMemberLoading" style="visibility:hidden">
					<img src="img/loading.gif" /> working...
				</span>
			</div>
			<hr />
			
			<b style="background-color:#f99">Wipe Database</b>
			<p>Wipes the entire database.  The schema is automatically rebuilt by the DAO.</p>
			<div id="wipeMessage" style="visibility:hidden"></div>
			<div>
				<button onclick="if (confirm('Are you sure you want to delete the entire database?  This cannot be undone.')) wipe()" id="wipeButton">Wipe</button>
				<span id="wipeLoading" style="visibility:hidden">
					<img src="img/loading.gif" /> working...
				</span>
			</div>
			<?php
		else:
			if ($loggedOut):
				?>Logged out.<br /><?php
			endif;
			?>
			<b>HypTools Admin</b><br /><br />
			<?php
			if ($wrongPassword):
				?><font color="red">Invalid login.</font><br /><?php
			endif;
			?>
			Enter password:
			<form action="index.php" method="POST">
			<input type="text" name="password" />
			<input type="submit" />
			</form>
		</div>
		<?php
	endif;
	?>
</body></html>

<?php

/**
 * Sends an AJAX response, then exits the script.
 * @param array $response the response
 */
function send($response){
	header("Content-type: application/json");
	
	$status = @$response['status'];
	if ($status != null){
		unset($response['status']);
		header('', true, $status);
	}
	
	echo json_encode($response);
	exit();
}

/**
 * Updates the alliance data in the database.
 * @return array the response
 */
function doUpdateAlliances(){
	$response = array();
	
	//find the data files
	$handler = opendir(__DIR__);
	$mostRecent = array();
	while ($file = readdir($handler)) {
		if (preg_match("/^(.*?)-(.*?)-alliances\\.txt\\.gz\$/", $file, $matches)){
			$game = $matches[1];
			$date = new DateTime($matches[2]);
			if (!isset($mostRecent[$game]) || $date->getTimestamp() > $mostRecent[$game]['date']->getTimestamp()){
				$mostRecent[$game] = array(
					'game'=>$game,
					'date'=>$date,
					'file'=>$file
				);
			}
		}
	}
	
	if (count($mostRecent) == 0){
		$response['status'] = 500;
		$response['errors'][] = 'No data files found.  Files must be in the format: "<gamename>-<yyymmdd>-alliances.txt.gz"';
		return $response;
	}
	
	//get all Hyperiums games
	$hapiDao = new HypToolsRealHapiDao();
	$games = $hapiDao->getGames();
	
	//init the DAO
	$dao = new HypToolsMySqlDao();

	foreach ($mostRecent as $dataFile){
		$gameName = $dataFile['game'];
		$file = $dataFile['file'];
		$date = $dataFile['date'];
		
		//make sure the game exists
		$game = null;
		foreach ($games as $g){
			if (strcasecmp($g->getName(), $gameName) == 0){
				$game = $g;
				break;
			}
		}
		if ($game == null){
			$response['warnings'][] = "Could not process \"$file\". Game with name \"$gameName\" does not exist.";
			continue;
		}
		
		//set up DAO
		$game = $dao->upsertGame($game->getName(), $game->getDescription());
		$dao->setGame($game);
		
		$dao->beginTransaction();
		try{
			$parser = new AllianceParser(__DIR__ . "/$file");
			$num = 0;
			while ($alliance = $parser->next()){
				$dao->upsertAlliance($alliance->getTag(), $alliance->getName(), $alliance->getPresident());
				$num++;
			}
			$dao->commit();
		} catch (Exception $e){
			$dao->rollBack();
			throw $e;
		}
		$response['processed'][] = array('count'=>$num, 'file'=>$file, 'date'=>$date->getTimestamp());
	}
	
	return $response;
}

/**
 * Makes a player the president of an alliance.
 * @param string $game the game name
 * @param string $player the player name
 * @param string $tag the alliance tag
 * @return array the response
 */
function doMakePresident($game, $player, $tag){
	$response = array();
	
	$game = trim($game);
	$player = trim($player);
	$tag = trim($tag);
	if ($game == ''){
		$response['errors'][] = 'No game specified.';
	}
	if ($player == ''){
		$response['errors'][] = 'No player specified.';
	}
	if ($tag == ''){
		$response['errors'][] = 'No tag specified.';
	}
	
	if (count($response['errors']) == 0){
		try{
			$dao = new HypToolsMySqlDao();
			$game = $dao->upsertGame($game, '');
			$dao->setGame($game);
			
			$alliance = $dao->selectAllianceByTag($tag);
			if ($alliance == null){
				$response['errors'][] = "Alliance [$tag] does not exist.";
			} else {
				$dao->upsertAlliance($alliance->tag, $alliance->name, $player);
			}
			
			$response['player'] = $player;
			$response['tag'] = $alliance->tag;
		} catch (Exception $e){
			$response['status'] = 500;
			$response['errors'][] = $e->getMessage();
		}
	}
	
	return $response;
}

/**
 * Makes a player a member of an alliance.
 * @param string $game the game name
 * @param string $player the player name
 * @param string $tag the alliance tag
 * @param boolean $permSubmit true to give the player submit access, false not to
 * @param boolean $permView true to give the player view access, false not to
 * @param boolean $permAdmin true to give the player admin access, false not to
 * @return array the response
 */
function doMakeMember($game, $player, $tag, $permSubmit, $permView, $permAdmin){
	$response = array();
	
	$game = trim($game);
	$player = trim($player);
	$tag = trim($tag);
	if ($game == ''){
		$response['errors'][] = 'No game specified.';
	}
	if ($player == ''){
		$response['errors'][] = 'No player specified.';
	}
	if ($tag == ''){
		$response['errors'][] = 'No tag specified.';
	}
	
	if (count($response['errors']) == 0){
		try{
			$dao = new HypToolsMySqlDao();
			$game = $dao->upsertGame($game, '');
			$dao->setGame($game);
			
			$alliance = $dao->selectAllianceByTag($tag);
			if ($alliance == null){
				$response['errors'][] = "Alliance [$tag] does not exist.";
			} else {
				$player = $dao->upsertPlayer($player);
				$permission = $dao->selectPermissionsByPlayerAndAlliance($player, $alliance);
				if ($permission == null){
					$permission = new Permission();
					$permission->player = $player;
					$permission->alliance = $alliance;
					$permission->permSubmit = $permSubmit;
					$permission->permView = $permView;
					$permission->permAdmin = $permAdmin;
					$dao->insertPermission($permission);
					$inserted = true;
				} else {
					$permission->permSubmit = $permSubmit;
					$permission->permView = $permView;
					$permission->permAdmin = $permAdmin;
					$dao->updatePermission($permission);
					$inserted = false;
				}
				
				$response['inserted'] = $inserted;
				$response['player'] = $player->name;
				$response['tag'] = $alliance->tag;
			}
		} catch (Exception $e){
			$response['status'] = 500;
			$response['errors'][] = $e->getMessage();
		}
	}
	
	return $response;
}

/**
 * Wipes the database.
 * @return array the response
 */
function doWipe(){
	$response = array();

	try{
		$dao = new HypToolsMySqlDao();
		$dao->dropAllTables();
	} catch (Exception $e){
		$response['status'] = 500;
		$response['errors'][] = $e->getMessage();
	}
	
	return $response;
}

?>