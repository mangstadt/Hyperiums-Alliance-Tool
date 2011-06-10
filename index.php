<?php

	error_reporting(E_ERROR) ;

	require_once dirname(__FILE__) .  '/lib/HAPI/bootstrap.php';

	use HAPI\HAPI;
	use HAPI\Game;
	
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
							<form id="loginForm" method="POST">
								<label for="game_select_input">Game Selection</label>
								<select name="game_select_input">
									<?php
										foreach (HAPI::getAllGames() as $game){
											$name = $game->getName();
											$index = $game->getIndex();
											if ($game->getState() == Game::STATE_RUNNING_OPEN) {
												echo '<option value="' . $index . '">' . $name . '</option>' ;
											}
										}
									?>
								</select>
								<br/>
								<label for="login_input">Your nickname</label>
								<input type="text" name="login_input" id="login_input" value=""/>
								<br/>
								<label for="hkey_input">Your HAPI Key</label>
								<input type="text" name="hkey_input" id="hkey_input" value=""/>
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