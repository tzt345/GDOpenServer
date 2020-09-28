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
		require_once "../lib/mainLib.php";
		$ep = new exploitPatch();
		$gs = new mainLib();
		if (!is_numeric($accountID) AND !is_numeric($levelID)) {
			return false;
		}
		$query = $db->prepare("SELECT isBanned FROM users WHERE extID = :id");
		$query->execute([':id' => $accountID]);
		if ($query->fetchColumn() != 0) {
			return false;
		}
		$comment = $ep->remove(strtolower($comment));
		$commentarray = explode(' ', $comment);
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
		require_once "../lib/spyc.php";
		$aliases = spyc_load_file("cmd/config/commands.yaml");
		$permissions = spyc_load_file("cmd/config/permissions.yaml");
		if (file_exists("cmd/".$commentarray[0].".php")) {
			if ($permissions[$commentarray[0]] == "admin" OR $permissions[$commentarray[0]] != "non-admin" OR !isset($permissions[$commentarray[0]])) {
				$commandFirstUpper = ucfirst(str_replace("un", "", $commentarray[0]));
				$commandConfig = "$"."command".$commandFirstUpper;
				if ($gs->checkPermission($accountID, "command".$commandFirstUpper) AND (eval("return $commandConfig == 1;") == 1)) {
					include "cmd/".$commentarray[0].".php";
				} else {
					return false;
				}
			} elseif ($permissions[$commentarray[0]] == "non-admin") {
				$commandFirstUpper = ucfirst(str_replace("un", "", $commentarray[0]));
				$commandConfig = "$"."command".$commandFirstUpper;
				if ($this->ownCommand($comment, $commentarray[0], $accountID, $targetExtID) AND (eval("return $commandConfig == 1;") == 1)) {
					include "cmd/".$commentarray[0].".php";
				} else {
					return false;
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
							return false;
						}
					} else {
						$commandFirstUpper = ucfirst(str_replace("un", "", $command));
						$commandConfig = "$"."command".$commandFirstUpper;
						if ($this->ownCommand($comment, $command, $accountID, $targetExtID) AND (eval("return $commandConfig == 1;") == 1)) {
							include "cmd/".$command.".php";
						} else {
							return false;
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
		require_once "../lib/mainLib.php";
		$ep = new exploitPatch();
		$gs = new mainLib();
		if(substr($command, 0, 7 + $prefixLen) == $prefix.'discord'){
			if(substr($command, 8 + $prefixLen, 5) == "accept"){
				$query = $db->prepare("UPDATE accounts SET discordID = discordLinkReq, discordLinkReq = '0' WHERE accountID = :accountID AND discordLinkReq <> 0");
				$query->execute([':accountID' => $accountID]);
				$query = $db->prepare("SELECT discordID, userName FROM accounts WHERE accountID = :accountID");
				$query->execute([':accountID' => $accountID]);
				$account = $query->fetch();
				$gs->sendDiscordPM($account["discordID"], "Your link request to " . $account["userName"] . " has been accepted!");
				return true;
			}
			if(substr($command, 8 + $prefixLen, 3) == "deny"){
				$query = $db->prepare("SELECT discordLinkReq, userName FROM accounts WHERE accountID = :accountID");
				$query->execute([':accountID' => $accountID]);
				$account = $query->fetch();
				$gs->sendDiscordPM($account["discordLinkReq"], "Your link request to " . $account["userName"] . " has been denied!");
				$query = $db->prepare("UPDATE accounts SET discordLinkReq = '0' WHERE accountID = :accountID");
				$query->execute([':accountID' => $accountID]);
				return true;
			}
			if(substr($command, 8 + $prefixLen, 6) == "unlink"){
				$query = $db->prepare("SELECT discordID, userName FROM accounts WHERE accountID = :accountID");
				$query->execute([':accountID' => $accountID]);
				$account = $query->fetch();
				$gs->sendDiscordPM($account["discordID"], "Your Discord account has been unlinked from " . $account["userName"] . "!");
				$query = $db->prepare("UPDATE accounts SET discordID = '0' WHERE accountID = :accountID");
				$query->execute([':accountID' => $accountID]);
				return true;
			}
		}
		return false;
	}
}
?>
