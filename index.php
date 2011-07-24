<?php
	require_once 'lib/bootstrap.php';

	use HAPI\HAPI;
	use HAPI\Game;
	use db\HypToolsMySqlDao;
	use db\HypToolsMockDao;
	use hapidao\HypToolsMockHapiDao;
	use hapidao\HypToolsRealHapiDao;

	$loggedOut = isset($_GET["loggedout"]);
	
	$mock = isset($_REQUEST['mock']);
	$hapiDao = $mock ? new HypToolsMockHapiDao() : new HypToolsRealHapiDao();
	$games = $hapiDao->getGames();
	
	$errors = array();
	if (count($_POST) > 0){
		$login = trim($_POST["login_input"]);
		$hkey = trim(@$_POST["hkey_input"]);
		$game_select = $_POST["game_select_input"];
		
		if (strlen($login) == 0){
			$errors[] = 'You must specify a nickname.';
		}
		
		if (!$mock && strlen($hkey) == 0){
			$errors[] = 'You must specify a HAPI Key.';
		}
		
		//find the game the user selected
		$game = null; //HAPI\Game
		foreach ($games as $g){
			if (strcasecmp($g->getName(), $game_select) == 0){
				$game = $g;
				break;
			}
		}
		if ($game == null){
			$errors[] = "Invalid game: \"$game_select\".";
		}

		if (count($errors) == 0){
			try{
				if ($mock){
					$hapiDao->setPlayerIdentifier($login);
				} else {
					//authenticate with Hyperiums
					$hapi = new HAPI($game_select, $login, $hkey, Env::$cacheDir . '/locks');
					$hapiDao->setPlayerIdentifier($hapi);
					Session::setHapi($hapi);
				}
				
				Session::setMockEnabled($mock);
				
				//init the DAO
				$dao = $mock ? new HypToolsMockDao($login) : new HypToolsMySqlDao();
				
				//insert game into DB if it's not there
				$game = $dao->upsertGame($game->getName(), $game->getDescription());
				
				//give game to DAO
				$dao->setGame($game);
				
				//get player info from database
				$playerId = $mock ? null : $hapi->getSession()->getPlayerId();
				$playerName = $mock ? $login : $hapi->getSession()->getPlayerName();
				$player = $dao->upsertPlayer($playerName, $playerId);
				$dao->updatePlayerLastLogin($player);
				Session::setPlayer($player);
				
				header('Location: home.php');
				exit();
			} catch (Exception $e){
				$errors[] = "Authentication failed with the following message: " . $e->getMessage();
			}
		}
	}
	
?>
<!DOCTYPE html>
<html lang="en">

	<head>

		<meta charset="utf-8" />
		<title>Hyperiums Alliance Tools</title>

		<link rel="stylesheet" href="css/hat.less" type="text/less" />
		
		<link  href="http://fonts.googleapis.com/css?family=Metrophobic:regular" rel="stylesheet" type="text/css" />

	</head>


	<body>
		
		<div id="logo"></div>
		
		<div id="main">
			
			<div id="header">
				<div class="h100"></div>
			</div>
			
			<div id="content">
				
				<div id="login">
					<div id="uL" class="topDiv"></div>
					<div id="uC" class="topDiv oneMore">Authentication</div>
					<div id="uR" class="topDiv"></div>
					<div id="middle" class="centerDiv">
						<div id="cL"></div>
						<div id="cC">
							<?php
							if ($loggedOut):
								?><p style="align:center; color:white;">Log off successful.</p><?php
							endif
							?>
							
							<?php
							if (count($errors) > 0):
								?><div style="color:red"><?php 
								foreach ($errors as $error):
									echo htmlspecialchars($error) . "<br />";
								endforeach
								?></div><?php
							endif
							?>
							<?php
							if ($mock):
								?><p style="color:yellow">Mock mode activated.</p><?php
							endif;
							?>
							<form id="loginForm" method="POST" action="index.php">
								<?php
								if ($mock):
									?><input type="hidden" name="mock" value="1" /><?php
								endif;
								?>
								<label for="game_select_input">Game Selection</label>
								<select name="game_select_input">
									<?php
										foreach ($games as $game){
											$name = $game->getName();
											if ($game->getState() == Game::STATE_RUNNING_OPEN) {
												$selected = ($name == @$_POST['game_select_input']) ? 'selected="selected"' : '';
												echo '<option value="' . htmlspecialchars($name) . '" ' . $selected . '>' . htmlspecialchars($name) . '</option>' ;
											}
										}
									?>
								</select>
								<br/>
								<label for="login_input">Your nickname</label>
								<input type="text" name="login_input" id="login_input" value="<?php echo htmlspecialchars(@$_POST['login_input'])?>"/>
								<br/>
								<label for="hkey_input">Your HAPI Key</label>
								<input <?php echo $mock ? 'disabled="disabled"' : ''?> type="text" name="hkey_input" id="hkey_input" value="<?php echo $mock ? 'mock mode' : htmlspecialchars(@$_POST['hkey_input'])?>"/>
								<br/>
								<input type="submit" value="Login"/>
							</form>
						</div>
						<div id="cR"></div>
					</div>
					<div id="bL" class="bottomDiv"></div>
					<div id="bR" class="bottomDiv"></div>
				</div>
				
			</div>
			
		</div>

		<div id="javascript">
			<script type="text/javascript" src="js/less-1.1.3.min.js"></script>
		</div>

	</body>

</html>