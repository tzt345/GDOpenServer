<?php
class Commands {
	private function ownCommand($comment, $command, $accountID, $targetExtID){
		chdir(dirname(__FILE__));
		include "../../config/commands.php";
		require_once "../../lib/mainLib.php";
		$gs = new mainLib();
		$commandInComment = strtolower($prefix.$command);
		$commandInPerms = ucfirst(strtolower($command));
		$commandlength = strlen($commandInComment);
		if(substr($comment, 0, $commandlength) == $commandInComment AND (($gs->checkPermission($accountID, "command".$commandInPerms."All") OR ($targetExtID == $accountID AND $gs->checkPermission($accountID, "command".$commandInPerms."Own"))))){
			return true;
		}
		return false;
	}
	public function doCommands($accountID, $comment, $levelID) {
		chdir(dirname(__FILE__));
		include "../lib/connection.php";
		include "../../config/commands.php";
		include "../../config/levels.php";
		require_once "../lib/exploitPatch.php";
		$ep = new exploitPatch();
		require_once "../lib/mainLib.php";
		$gs = new mainLib();
		require_once "../lib/spyc.php";
		if (!is_numeric($accountID) AND !is_numeric($levelID)) {
			exit("temp_0_Error: The level is either corrupted, or you are using hacks as a non-gold user.");
		}
		$query = $db->prepare("SELECT isBanned FROM users WHERE extID = :id");
		$query->execute([':id' => $accountID]);
		if ($query->fetchColumn() != 0) {
			exit("temp_0_Error: You are banned, meaning you cannot take any moderational actions.");
		}
		$comment = $ep->remove(strtolower($comment));
		$commentarray = explode(" ", $comment);
		$prefixLen = strlen($prefix);
		if (substr($comment, 0, $prefixLen) == $prefix) {
			$commentarray[0] = str_replace($prefix, "", $commentarray[0]);
		} else {
			return false;
		}
		$uploadDate = time();
		// Getting level owner's account ID
		$query2 = $db->prepare("SELECT extID FROM levels WHERE levelID = :id");
		$query2->execute([':id' => $levelID]);
		$targetExtID = $query2->fetchColumn();
		$aliases = spyc_load_file("cmd/config/commands.yaml");
		$permissions = spyc_load_file("cmd/config/permissions.yaml");
		if (file_exists("cmd/".$commentarray[0].".php")) {
			if ($permissions[$commentarray[0]] == "admin" OR $permissions[$commentarray[0]] != "non-admin" OR !isset($permissions[$commentarray[0]])) {
				$commandFirstUpper = ucfirst(str_replace("un", "", $commentarray[0]));
				$commandConfig = "$"."command".$commandFirstUpper;
				if ($gs->checkPermission($accountID, "command".$commandFirstUpper) AND (eval("return $commandConfig == 1;") == 1)) {
					include "cmd/".$commentarray[0].".php";
				} else {
					exit("temp_0_Error: You do not have proper permission to use this command.");
				}
			} elseif ($permissions[$commentarray[0]] == "non-admin") {
				$commandPerm = str_replace("un", "", $commentarray[0]);
				$commandFirstUpper = ucfirst($commandPerm);
				$commandConfig = "$"."command".$commandFirstUpper;
				if ($this->ownCommand($comment, $commandPerm, $accountID, $targetExtID) AND (eval("return $commandConfig == 1;") == 1)) {
					include "cmd/".$commentarray[0].".php";
				} else {
					exit("temp_0_Error: You do not have proper permission to use this command.");
				}
			}
		} else {
			foreach($aliases as $command => $alias) {
				if ($aliases[$command][$commentarray[0]]) {
					if ($permissions[$command] == "admin" OR $permissions[$command] != "non-admin" OR !isset($permissions[$command])) {
						$commandFirstUpper = ucfirst(str_replace("un", "", $command));
						$commandConfig = "$"."command".$commandFirstUpper;
						if ($gs->checkPermission($accountID, "command".$commandFirstUpper) AND (eval("return $commandConfig == 1;") == 1)) {
							include "cmd/".$command.".php";
						} else {
							exit("temp_0_Error: You do not have proper permission to use this command.");
						}
					} else {
						$commandPerm = str_replace("un", "", $command);
						$commandFirstUpper = ucfirst($commandPerm);
						$commandConfig = "$"."command".$commandFirstUpper;
						if ($this->ownCommand($comment, $commandPerm, $accountID, $targetExtID) AND (eval("return $commandConfig == 1;") == 1)) {
							include "cmd/".$command.".php";
						} else {
							exit("temp_0_Error: You do not have proper permission to use this command.");
						}
					}
				}
			}
			return false;
		}
	}
	public function doProfileCommands($accountID, $command){
		include dirname(__FILE__)."/../lib/connection.php";
		require_once "../lib/exploitPatch.php";
		$ep = new exploitPatch();
		require_once "../lib/mainLib.php";
		$gs = new mainLib();
		include "../../config/commands.php";
		$prefixLen = strlen($prefix);
		if(substr($command, 0, 7 + $prefixLen) == $prefix."discord"){
			if(substr($command, 8 + $prefixLen, 6) == "accept"){
				include "cmd/discord/accept.php";
				return true;
			}
			if(substr($command, 8 + $prefixLen, 4) == "deny"){
				include "cmd/discord/deny.php";
				return true;
			}
			if(substr($command, 8 + $prefixLen, 6) == "unlink"){
				include "cmd/discord/unlink.php";
				return true;
			}
		}
		return false;
	}
}
?>
