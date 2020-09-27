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
		$aliases = yaml_parse("cmd/commands.yaml");
		$permissions = yaml_parse("cmd/permissions.yaml");
		if (file_exists("cmd/".$commentarray[0].".php")) {
			if ($permissions[$commentarray[0]] == "admin" OR $permissions[$commentarray[0]] != "non-admin") {
				$commandFirstUpper = ucfirst(str_replace("un", "", $commentarray[0]));
				$commandConfig = "$"."command".$commandFirstUpper;
				if ($gs->checkPermission($accountID, "command".$commandFirstUpper) AND (eval($commandConfig) == 1) {
					include "cmd/".$commentarray[0].".php";
				} else {
					return false;
				}
			} elseif ($permissions[$commentarray[0]] == "non-admin") {
				$commandFirstUpper = ucfirst(str_replace("un", "", $commentarray[0]));
				$commandConfig = "$"."command".$commandFirstUpper;
				if ($this->ownCommand($comment, $commentarray[0], $accountID, $targetExtID) AND (eval($commandConfig) == 1) {
					include "cmd/".$commentarray[0].".php";
				} else {
					return false;
				}
			}
		} else {
			foreach($aliases as $command => $alias) {
				if ($aliases[$command][$commentarray[0]]) {
					if ($permissions[$command] == "admin" OR $permissions[$command] != "non-admin") {
						$commandFirstUpper = ucfirst(str_replace("un", "", $command));
						$commandConfig = "$"."command".$commandFirstUpper;
						if ($gs->checkPermission($accountID, "command".$commandFirstUpper) AND (eval($commandConfig) == 1) {
							include "cmd/".$command.".php";
						} else {
							return false;
						}
					} else {
						$commandFirstUpper = ucfirst(str_replace("un", "", $command));
						$commandConfig = "$"."command".$commandFirstUpper;
						if ($this->ownCommand($comment, $command, $accountID, $targetExtID) AND (eval($commandConfig) == 1) {
							include "cmd/".$command.".php";
						} else {
							return false;
						}
					}
				}
			}
			return false;
		}
		/* if(substr($comment, 0, 4 + $prefixLen) == $prefix.'rate' AND $gs->checkPermission($accountID, "commandRate") AND $commandRate == 1){
			return rate($gs, $commentarray, $uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 6 + $prefixLen) == $prefix.'unrate' AND $gs->checkPermission($accountID, "commandRate") AND $commandUnrate == 1){
			return unrate($gs, $commentarray, $uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 7 + $prefixLen) == $prefix.'feature' AND $gs->checkPermission($accountID, "commandFeature") AND $commandFeature == 1){
			return feature($uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 9 + $prefixLen) == $prefix.'unfeature' AND $gs->checkPermission($accountID, "commandFeature") AND $commandUnfeature == 1){
			return unfeature($uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 4 + $prefixLen) == $prefix.'epic' AND $gs->checkPermission($accountID, "commandEpic") AND $commandEpic == 1){
			return epic($uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 6 + $prefixLen) == $prefix.'unepic' AND $gs->checkPermission($accountID, "commandEpic") AND $commandUnepic == 1){
			return unepic($uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 4 + $prefixLen) == $prefix.'hall' AND $gs->checkPermission($accountID, "commandEpic") AND $commandHall == 1 AND $epicInHall == 0){
			return hall($uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 6 + $prefixLen) == $prefix.'unhall' AND $gs->checkPermission($accountID, "commandEpic") AND $commandUnhall == 1 AND $epicInHall == 0){
			return unhall($uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 5 + $prefixLen) == $prefix.'magic' AND $gs->checkPermission($accountID, "commandMagic") AND $commandMagic == 1 AND $isMagicSectionManual == 1){
			return magic($uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 7 + $prefixLen) == $prefix.'unmagic' AND $gs->checkPermission($accountID, "commandMagic") AND $commandUnmagic == 1 AND $isMagicSectionManual == 1){
			return unmagic($uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 11 + $prefixLen) == $prefix.'verifycoins' AND $gs->checkPermission($accountID, "commandVerifycoins") AND $commandVerifyCoins == 1){
			return verifycoins($uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 13 + $prefixLen) == $prefix.'unverifycoins' AND $gs->checkPermission($accountID, "commandVerifycoins") AND $commandUnverifyCoins == 1){
			return unverifycoins($uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 5 + $prefixLen) == $prefix.'daily' AND $gs->checkPermission($accountID, "commandDaily") AND $commandDaily == 1){
			return daily($uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 6 + $prefixLen) == $prefix.'weekly' AND $gs->checkPermission($accountID, "commandWeekly") AND $commandWeekly == 1){
			return weekly($uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 5 + $prefixLen) == $prefix.'delet' AND $gs->checkPermission($accountID, "commandDelete") AND $commandDelete == 1){
			return delete($uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 6 + $prefixLen) == $prefix.'setacc' AND $gs->checkPermission($accountID, "commandSetacc") AND $commandSetAcc == 1){
			return setacc($commentarray, $uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 11 + $prefixLen) == $prefix.'disablesong' AND $gs->checkPermission($accountID, "commandDisablesong") AND $commandDisableSong == 1){
			return disablesong($commentarray, $uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 10 + $prefixLen) == $prefix.'enablesong' AND $gs->checkPermission($accountID, "commandDisablesong") AND $commandEnableSong == 1){
			return enablesong($commentarray, $uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 3 + $prefixLen) == $prefix.'ban' AND $gs->checkPermission($accountID, "commandBan") AND $commandBan == 1){
			return ban($comment, $commentarray, $uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 10 + $prefixLen) == $prefix.'commentban' AND $gs->checkPermission($accountID, "commandCommentban") AND $commandCommentBan == 1){
			return commentban($comment, $commentarray, $uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 5 + $prefixLen) == $prefix.'unban' AND $gs->checkPermission($accountID, "commandBan") AND $commandUnban == 1){
			return unban($commentarray, $uploadDate, $accountID, $levelID);
		}
		if(substr($comment, 0, 12 + $prefixLen) == $prefix.'uncommentban' AND $gs->checkPermission($accountID, "commandCommentban") AND $commandUncommentBan == 1){
			return uncommentban($commentarray, $uploadDate, $accountID, $levelID);
		}
		if($this->ownCommand($comment, "rename", $accountID, $targetExtID) AND $commandRename == 1){
			return renamelevel($comment, $uploadDate, $accountID, $levelID);
		}
		if($this->ownCommand($comment, "pass", $accountID, $targetExtID) AND $commandPass == 1){
			return pass($commentarray, $uploadDate, $accountID, $levelID);
		}
		if($this->ownCommand($comment, "song", $accountID, $targetExtID) AND $commandSong == 1){
			return song($comment, $uploadDate, $accountID, $levelID);
		}
		if($this->ownCommand($comment, "description", $accountID, $targetExtID) AND $commandDescription == 1){
			return description($comment, $commentarray, $uploadDate, $accountID, $levelID);
		}
		if($this->ownCommand($comment, "public", $accountID, $targetExtID) AND $commandPublic == 1){
			return publiclevel($uploadDate, $accountID, $levelID);
		}
		if($this->ownCommand($comment, "unlist", $accountID, $targetExtID) AND $commandUnlist == 1){
			return unlist($uploadDate, $accountID, $levelID);
		}
		if($this->ownCommand($comment, "sharecp", $accountID, $targetExtID) AND $commandShareCP == 1){
			return sharecp($targetExtID, $commentarray, $uploadDate, $accountID, $levelID);
		}
		if($this->ownCommand($comment, "noshare", $accountID, $targetExtID) AND $commandNoShare == 1){
			return noshare($uploadDate, $accountID, $levelID);
		}
		if($this->ownCommand($comment, "ldm", $accountID, $targetExtID) AND $commandLDM == 1){
			return ldm($uploadDate, $accountID, $levelID);
		}
		if($this->ownCommand($comment, "unldm", $accountID, $targetExtID) AND $commandUnLDM == 1){
			return unldm($uploadDate, $accountID, $levelID);
		}
		return false; */
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
