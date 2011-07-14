<?php
require_once 'lib/bootstrap.php';
use db\HypToolsMySqlDao;
use db\HypToolsMockDao;
use db\JoinLog;
use db\Permission;

//values used for the none/accept/deny dropdown list in the auth requests section
define('ACTION_NONE', 0);
define('ACTION_ACCEPT', 1);
define('ACTION_REJECT', 2); 

//has the player logged in?
session_start();
$hapi = @$_SESSION['hapi'];
if ($hapi == null){
	header('Location: index.php');
	exit();
}

//was a tag specified?
$allianceTag = @$_REQUEST['tag'];
if ($allianceTag == null){
	header('Location: home.php');
	exit();
}

//init DAO
$player = $_SESSION['player'];
$mock = $_SESSION['mock'];
$dao = $mock ? new HypToolsMockDao($player->game) : new HypToolsMySqlDao($player->game);

//get the specified alliance
$alliance = $dao->selectAllianceByTag($allianceTag);
if ($alliance == null){
	//alliance does not exist
	header('Location: home.php');
	exit();
}

//get the permissions the player has within this alliance
$playerPermissions = $dao->selectPermissionsByPlayerAndAlliance($player, $alliance);
if ($playerPermissions == null){
	//player does not belong to this alliance
	header('Location: home.php');
	exit();
} else if (!$playerPermissions->permAdmin){
	//player does not have permission to view admin page
	header('Location: submit.php?tag=' . urlencode($alliance->tag));
	exit();
}

//user submitted a form
$method = @$_POST['method'];
if ($method != null){
	if ($method == "approveJoinRequests"){
		//user submitted the pending auth requests form
		
		//organize POST params into an array (key = joinRequestId, value = fields of that join request)
		$requestsToHandle = array();
		foreach ($_POST as $key=>$value){
			if (preg_match("/^(action|submit|view|admin)(\\d+)\$/", $key, $matches)){
				$joinRequestId = $matches[2];
				$fieldName = $matches[1];
				$requestsToHandle[$joinRequestId][$fieldName] = $value;
			}
		}
		
		//update DB
		$dao->beginTransaction();
		try{
			$accepted = 0;
			$rejected = 0;
			foreach ($requestsToHandle as $joinRequestId=>$fields){
				$joinRequest = $dao->selectJoinRequestById($joinRequestId);
				if ($joinRequest->alliance->id == $alliance->id){ //make sure that this auth request belongs to this alliance
					$action = $fields['action'];
					if ($action == ACTION_ACCEPT){
						$p = new Permission();
						$p->player = $joinRequest->player;
						$p->alliance = $joinRequest->alliance;
						$p->permSubmit = isset($fields['submit']);
						$p->permView = isset($fields['view']);
						$p->permAdmin = isset($fields['admin']);
						$dao->insertPermission($p);
						$dao->deleteJoinRequest($joinRequestId);
						$dao->insertJoinLog($joinRequest->player, $joinRequest->alliance, JoinLog::EVENT_ACCEPTED);
						$accepted++;
					} else if ($action == ACTION_REJECT){
						$dao->deleteJoinRequest($joinRequestId);
						$dao->insertJoinLog($joinRequest->player, $joinRequest->alliance, JoinLog::EVENT_REJECTED);
						$rejected++;
					} else if ($action == ACTION_NONE){
						//do nothing
					}
				}
			}
			$dao->commit();
			
			$message = "Operation complete: $accepted request(s) accepted, $rejected request(s) rejected.";
		} catch (Exception $e){
			$dao->rollBack();
			throw $e;
		}
	} else if ($method == "members"){
		//user submitted the member permissions form
		
		//organize POST params into an array (key = permissionId, value = fields of that permission)
		$requestsToHandle = array();
		foreach ($_POST as $key=>$value){
			if (preg_match("/^(revoke|submit|view|admin)(\\d+)\$/", $key, $matches)){
				$joinRequestId = $matches[2];
				$fieldName = $matches[1];
				$requestsToHandle[$joinRequestId][$fieldName] = $value;
			}
		}
		
		//update DB
		$dao->beginTransaction();
		try{
			foreach ($requestsToHandle as $permissionId=>$fields){
				$permission = $dao->selectPermissionById($permissionId);
				if ($permission->alliance->id == $alliance->id){ //make sure that this permission belongs to this alliance
					if (isset($fields['revoke'])){
						$dao->deletePermission($permissionId);
						$dao->insertJoinLog($permission->player, $permission->alliance, JoinLog::EVENT_REMOVED);
					} else {
						$permission->permSubmit = isset($fields['submit']);
						$permission->permView = isset($fields['view']);
						$permission->permAdmin = isset($fields['admin']);
						$dao->updatePermission($permission);
					}
				}
			}
			$dao->commit();
			
			//check the player's permissions again, incase he changed his own permissions
			$playerPermissions = $dao->selectPermissionsByPlayerAndAlliance($player, $alliance);
			if ($playerPermissions == null){
				//player does not belong to this alliance
				header('Location: home.php');
				exit();
			} else if (!$playerPermissions->permAdmin){
				//player does not have permission to view admin page
				header('Location: submit.php?tag=' . urlencode($alliance->tag));
				exit();
			}
			
			$message = "Member permissions updated.";
		} catch (Exception $e){
			$dao->rollBack();
			throw $e;
		}
	} else if ($method == "authPlayers"){
		$players = trim($_POST['players']);
		if (strlen($players) > 0){
			$players = preg_split("/\\s*,\\s*/", $players);
			
			//update DB
			$dao->beginTransaction();
			try{
				$new = 0;
				$already = 0;
				foreach ($players as $p){
					$player = $dao->upsertPlayer($p);
					$permission = $dao->selectPermissionsByPlayerAndAlliance($player, $alliance);
					if ($permission == null){
						//delete the player's join request if he made one
						$dao->deleteJoinRequestByPlayerAndAlliance($player, $alliance);
						
						$p = new Permission();
						$p->player = $player;
						$p->alliance = $alliance;
						$p->permSubmit = true;
						$p->permView = false;
						$p->permAdmin = false;
						$dao->insertPermission($p);
						$dao->insertJoinLog($player, $alliance, JoinLog::EVENT_ACCEPTED);
						$new++;
					} else {
						//player already belongs to this alliance
						$already++;
					}
				}
				$dao->commit();
				
				$message = "$new new player(s) authenticated. $already player(s) were already authenticated.";
			} catch (Exception $e){
				$dao->rollBack();
				throw $e;
			}
		}
	}
}

//get the alliances the player belongs to
$playerAlliances = $dao->selectPermissionsByPlayer($player);

//get the pending auth requests to this alliance
$authRequests = $dao->selectJoinRequestsByAlliance($alliance);

//get all the members of the alliance
$memberPermissions = $dao->selectPermissionsByAlliance($alliance);

?>
<!DOCTYPE html>
<html lang="en">

	<head>

		<meta charset="utf-8" />
		<title>Hyperiums Alliance Tools</title>

		<link rel="stylesheet" href="css/hat.less" type="text/less" />
		
		<link  href="http://fonts.googleapis.com/css?family=Metrophobic:regular" rel="stylesheet" type="text/css" />
		
		<script>
			/**
			 * Updates the row of an authentication request when its "action" dropdown value changes.
			 * @param joinRequestId the ID of the join request
			 */
			function updateAuthRequestRow(joinRequestId){
				var dropdown = document.getElementById("action" + joinRequestId);
				var selectedIndex = dropdown.selectedIndex;

				//should the row be disabled?
				var disableRow = (selectedIndex == <?php echo ACTION_NONE?> || selectedIndex == <?php echo ACTION_REJECT?>);

				//set the background color
				var bgColor;
				if (selectedIndex == <?php echo ACTION_ACCEPT?>){
					bgColor = "#090";
				} else if (selectedIndex == <?php echo ACTION_REJECT?>){
					bgColor = "#900";
				} else {
					bgColor = "transparent";
				}
				
				//document.getElementById("submit" + joinRequestId).disabled = disableRow; //this is always disabled
				document.getElementById("view" + joinRequestId).disabled = disableRow;
				document.getElementById("admin" + joinRequestId).disabled = disableRow;
				document.getElementById("authRow" + joinRequestId).style.backgroundColor = bgColor;
			}

			/**
			 * Toggles whether a player will have his auth revoked.
			 * @param permissionId the permission ID of the player
			 */
			function toggleRevoke(permissionId){
				var checkbox = document.getElementById("members_revoke" + permissionId);
				checkbox.checked = !checkbox.checked; //toggle checkbox

				//is revoked checked?
				var revoked = checkbox.checked;

				//get background color of the row
				var bgColor;
				if (revoked){
					bgColor = "#900";
				} else {
					bgColor = "transparent";
				}
				
				//document.getElementById("members_submit" + permissionId).disabled = revoked; //this is always disabled
				document.getElementById("members_view" + permissionId).disabled = revoked;
				document.getElementById("members_admin" + permissionId).disabled = revoked;
				document.getElementById("members_row" + permissionId).style.backgroundColor = bgColor;
			}

			/**
			 * Toggles the value of a checkbox;
			 * @param id the checkbox's ID 
			 */
			function toggleCheckbox(id){
				var c = document.getElementById(id);
				if (!c.disabled){
					c.checked = !c.checked;
				}
			}

			/**
			 * Called when the member permissions form is reset.
			 */
			function revertMemberPermissions(){
				//clear background color
				var rows = document.getElementsByTagName("tr");
				for (var i = 0; i < rows.length; i++){
					var row = rows[i];
					if (row.id.indexOf("members_row") == 0){
						row.style.backgroundColor = "transparent";
					}
				}

				//enable all checkboxes
				var inputElements = document.getElementsByTagName("input");
				for (var i = 0; i < inputElements.length; i++){
					var input = inputElements[i];
					if (input.type == "checkbox" && (input.id.indexOf("members_view") == 0 || input.id.indexOf("members_admin") == 0)){ //do not enable "submit" checkboxes because these are always disabled
						input.disabled = false;
					}
				}
			}

			/**
			 * Called when the member permissions form is submitted.
			 * @return true to submit the form, false not to
			 */
			function onsubmitMembers(){
				//determine if the user chose to remove any players
				var rejected = new Array();
				var inputElements = document.getElementsByTagName("input");
				var revokePrefix = "members_revoke";
				var revokingYourself = false;
				var revokingYourOwnAdminPerm = false;
				for (var i = 0; i < inputElements.length; i++){
					var input = inputElements[i];
					if (input.type == "checkbox" && input.id.indexOf(revokePrefix) == 0 && input.checked){
						var id = input.id.substring(revokePrefix.length);
						var name = document.getElementById("members_name" + id).innerHTML;
						rejected.push(name);
						if (id == <?php echo $playerPermissions->id?>){
							//warn the player if he is revoking auth on himself
							revokingYourself = true;
						}
					}
				}

				//display confirmation dialog if user is removing players
				if (rejected.length > 0){
					var msg = "You have chosen to revoke authentication from the following players:\n\n";
					msg += rejected[0];
					for (var i = 1; i < rejected.length; i++){
						msg += ", " + rejected[i];
					}
					msg += "\n\nAre you sure you want to do this?";
					
					if (confirm(msg)){
						//warn the user if he is removing himself from the alliance
						if (revokingYourself){
							return confirm("You have chosen to revoke your own alliance authentication.  If you do this, you will not be able to access your alliance anymore.\n\nAre you sure you want to do this?");
						}
					} else {
						return false;
					}
				}

				//warn the player if he is revoking his own admin privledges
				var checkbox = document.getElementById("members_admin<?php echo $playerPermissions->id?>");
				if (!checkbox.checked){
					return confirm("You have chosen to revoke your own admin priviledges.  If you do this, you will no longer have access to this admin page.\n\nAre you sure you want to do this?");
				}

				return true;
			}
		</script>

	</head>


	<body>
		
		<div id="logo"></div>
		
		<div id="main">
			
			<div id="header">
				<div class="h100"></div>
			</div>
			
			<div id="content" style="color:white">
			
				<div>
					Hello, <b><?php echo htmlspecialchars($player->name)?>!</b>
				</div>
			
				<div>
					<a href="home.php">Home</a>
					<a href="logout.php">Logout</a>
				</div>
				
				<div style="border-bottom:3px solid #fff; padding-bottom:5px">
					Alliances: 
					<?php
					if (count($playerAlliances) == 0):
						?><i>none</i><?php
					else:
						for ($i = 0; $i < count($playerAlliances); $i++):
							$a = $playerAlliances[$i];
							if ($i > 0) echo ' | ';
							?><a href="submit.php?tag=<?php echo urlencode($a->alliance->tag) ?>">[<?php echo htmlspecialchars($a->alliance->tag) ?>]</a><?php
						endfor;
					endif;
					?>
				</div>
				
				<div align="center">
					<div><b>[<?php echo htmlspecialchars($alliance->tag)?>]</b></div>
					<?php
					$links = array();
					if ($playerPermissions->permSubmit):
						$links[] = '<a href="submit.php?tag=' . urlencode($alliance->tag) . '">Submit Data</a>';
					endif;
					if ($playerPermissions->permView):
						$links[] = '<a href="view.php?tag=' . urlencode($alliance->tag) . '">View Alliance Data</a>';
					endif;
					if ($playerPermissions->permAdmin):
						$links[] = '<b><a href="admin.php?tag=' . urlencode($alliance->tag) . '">Admin</a></b>';
					endif;
					echo implode(" | ", $links);
					?>
				</div>
				
				<?php
				if (isset($message)):
					?><div style="color:green; font-weight:bold; text-align:center"><?php echo $message?></div><?php
				endif;
				?>
				
				<div class="block">
					<h1>Authentication Requests</h1>
					<div>
						<p>The following players have requested authentication with the [<b><?php echo htmlspecialchars($alliance->tag)?></b>] alliance.</p>
						<?php
						if (count($authRequests) == 0):
							?><i>None</i><?php
						else:
							?>
							<form action="admin.php?tag=<?php echo urlencode($allianceTag)?>" method="post">
								<input type="hidden" name="method" value="approveJoinRequests" />
								<input type="hidden" name="tag" value="<?php echo htmlspecialchars($allianceTag)?>" />
								<table cellpadding="5">
									<thead>
										<tr>
											<th rowspan="2">Player</th>
											<th rowspan="2">Date</th>
											<th rowspan="2">Action</th>
											<th colspan="3">Permissions</th>
										</tr>
										<tr>
											<th>Submit Data</th>
											<th>View Alliance Data</th>
											<th>Admin</th>
										</tr>
									</thead>
									<tbody>
									<?php 
									for ($i = 0; $i < count($authRequests); $i++):
										$r = $authRequests[$i];
										?>
										<tr id="authRow<?php echo $r->id?>">
											<td><?php echo htmlspecialchars($r->player->name)?></td>
											<td><?php echo htmlspecialchars($r->requestDate->format('Y-m-d G:i T'))?></td>
											<td>
												<select onchange="updateAuthRequestRow(<?php echo $r->id?>)" name="action<?php echo htmlspecialchars($r->id)?>" id="action<?php echo htmlspecialchars($r->id)?>">
													<option value="<?php echo ACTION_NONE;?>" selected="selected">None</option>
													<option value="<?php echo ACTION_ACCEPT;?>">Accept</option>
													<option value="<?php echo ACTION_REJECT;?>">Reject</option>
												</select>
											</td>
											<td align="center">
												<?php //add hidden field because the "submit" checkbox is disabled ?>
												<input type="hidden"
												name="submit<?php echo $r->id?>"
												value="1" />
												
												<input type="checkbox"
												name="submit<?php echo $r->id?>"
												id="submit<?php echo $r->id?>"
												value="1"
												checked="checked"
												disabled="disabled"/>
											</td>
											<td align="center" onclick="toggleCheckbox('view<?php echo $r->id?>')">
												<input type="checkbox"
												name="view<?php echo $r->id?>"
												id="view<?php echo $r->id?>"
												value="1"
												onclick="this.checked = !this.checked"
												disabled="disabled" />
											</td>
											<td align="center" onclick="toggleCheckbox('admin<?php echo $r->id?>')">
												<input type="checkbox"
												name="admin<?php echo $r->id?>"
												id="admin<?php echo $r->id?>"
												value="1"
												onclick="this.checked = !this.checked"
												disabled="disabled" />
											</td>
										</tr>
										<?php
									endfor;
									?>
									
									</tbody>
								</table>
								<div align="right"><input type="submit" value="Update Auth Requests" class="button"/></div>
							</form>
							<?php
						endif;
						?>
					</div>
				</div>
				
				<div class="block">
					<h1>Member Permissions</h1>
					<div>
						<p>Adjust the permissions of your alliance members to give them greater or lesser access to [<b><?php echo htmlspecialchars($alliance->tag)?></b>] intelligence.</p>
						<?php
						if (count($memberPermissions) == 0):
							?><i>None</i><?php
						else:
							?>
							<form action="admin.php?tag=<?php echo urlencode($allianceTag)?>" method="post" onsubmit="return onsubmitMembers()">
								<input type="hidden" name="method" value="members" />
								<input type="hidden" name="tag" value="<?php echo htmlspecialchars($allianceTag)?>" />
								<table cellpadding="5">
									<thead>
										<tr>
											<th rowspan="2">Player</th>
											<th rowspan="2">Date Joined</th>
											<th colspan="3">Permissions</th>
											<th rowspan="2">Revoke Authentication</th>
										</tr>
										<tr>
											<th>Submit Data</th>
											<th>View Alliance Data</th>
											<th>Admin</th>
										</tr>
									</thead>
									<tbody>
									<?php 
									for ($i = 0; $i < count($memberPermissions); $i++):
										$p = $memberPermissions[$i];
										?>
										<tr id="members_row<?php echo $p->id?>">
											<td id="members_name<?php echo $p->id?>"><?php echo htmlspecialchars($p->player->name)?></td>
											<td><?php echo htmlspecialchars($p->joinDate->format('Y-m-d G:i T'))?></td>
											<td align="center">
												<?php //add hidden field because the "submit" checkbox is disabled ?>
												<input type="hidden"
												name="submit<?php echo $p->id?>"
												value="1" />
												
												<input type="checkbox"
												name="submit<?php echo $p->id?>"
												id="members_submit<?php echo $p->id?>"
												value="1"
												<?php echo $p->permSubmit ? 'checked="checked"' : ''?>
												disabled="disabled"/>
											</td>
											<td align="center" onclick="toggleCheckbox('members_view<?php echo $p->id?>')">
												<input type="checkbox"
												name="view<?php echo $p->id?>"
												id="members_view<?php echo $p->id?>"
												onclick="this.checked = !this.checked"
												value="1"
												<?php echo $p->permView ? 'checked="checked"' : ''?> />
											</td>
											<td align="center" onclick="toggleCheckbox('members_admin<?php echo $p->id?>')">
												<input type="checkbox"
												name="admin<?php echo $p->id?>"
												id="members_admin<?php echo $p->id?>"
												onclick="this.checked = !this.checked"
												value="1"
												<?php echo $p->permAdmin ? 'checked="checked"' : ''?> />
											</td>
											<td align="center" onclick="toggleRevoke(<?php echo $p->id?>)">
												<input type="checkbox"
												name="revoke<?php echo $p->id?>"
												id="members_revoke<?php echo $p->id?>"
												onclick="this.checked = !this.checked"
												value="1" />
											</td>
										</tr>
										<?php
									endfor;
									?>
									
									</tbody>
								</table>
								<div align="right"><input type="reset" value="Revert" class="button" onclick="revertMemberPermissions()" /><input type="submit" value="Update Member Permissions" class="button" /></div>
							</form>
							<?php
						endif;
						?>
					</div>
				</div>
				
				<div class="block">
					<h1>Authenticate Players</h1>
					<div>
						<p>Give alliance authentication to multiple players at once.  Player names must be separated by commas.</p>
						<form action="admin.php?tag=<?php echo urlencode($allianceTag)?>" method="post">
							<input type="hidden" name="method" value="authPlayers" />
							<input type="hidden" name="tag" value="<?php echo htmlspecialchars($allianceTag)?>" />
							<textarea rows="10" cols="50" name="players"></textarea>
							<div align="right"><input type="submit" value="Authenticate" class="button" /></div>
						</form>
					</div>
				</div>
			</div>
			
		</div>

		<div id="javascript">
			<script type="text/javascript" src="js/less-1.1.3.min.js"></script>
		</div>

	</body>

</html>