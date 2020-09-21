<?php
function commentban($comment, $commentarray, $uploadDate, $accountID, $levelID) {
	include dirname(__FILE__)."/../../lib/connection.php";
	$commandName = $commentarray[0];
	if (isset($commentarray[1])) {
		$userName = $commentarray[1];
	} else {
		return false;
	}
	if (isset($commentarray[2])) {
		$timeArg = $commentarray[2];
		$timeSuffix = substr($time, -1);
		switch($timeSuffix) {
			case "m":
				$time = int(trim($timeArg, "m")) * 60;
				break;
			case "h":
				$time = int(trim($timeArg, "h")) * 3600;
				break;
			case "d":
				$time = int(trim($timeArg, "d")) * 86400;
				break;
			default:
				try {
					$time = time() + int($timeArg);
				} catch (Exception $e) {
					return false;
				}
				break;
		}
		if ($time > 0) {
			$time = time() + $time;
		} elseif ($time == 0) {
			$time = 0;
		} else {
			return false;
		}
	} else {
		$time = 0;
	}
	if (isset($commentarray[3])) {
		$reason = str_replace($prefix.$commandName.$userName.$banTypeArg.$timeArg." ", "", $comment);;
	} else {
		$reason = "No reason specified";
	}
	$query = $db->prepare("SELECT accountID FROM accounts WHERE userName = :userName OR accountID = :userName LIMIT 1");
	$query->execute([':userName' => $userName]);
	$targetAcc = $query->fetchColumn();
	if($query->rowCount() == 0 OR $targetAcc == $accountID){
		return false;
	}
	$query = $db->prepare("SELECT userID, isCommentBanned FROM users WHERE extID = :extID LIMIT 1");
	$query->execute([':extID' => $targetAcc]);
	$result = $query->fetch();
	$userID = $result["userID"];
	if ($result["isCommentBanned"] == 1) {
		return false;
	}
	$query = $db->prepare("UPDATE users SET isCommentBanned = 1, commentBanTime = :time, commentBanReason = :reason WHERE userID=:userID");
	$query->execute([':userID' => $userID, ':time' => $time, ':reason' => $reason]);
	$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (15, 4, :value, 1, :timestamp, :id)");
	$query->execute([':value' => $userName, ':timestamp' => $uploadDate, ':id' => $accountID]);
	return true;
}
?>