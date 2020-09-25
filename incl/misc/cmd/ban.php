<?php
function ban($comment, $commentarray, $uploadDate, $accountID, $levelID) {
	include dirname(__FILE__)."/../../lib/connection.php";
	$commandName = $commentarray[0];
	if (isset($commentarray[1])) {
		$userName = $commentarray[1];
	} else {
		return false;
	}
	if (isset($commentarray[2]) AND is_numeric($commentarray[2])) {
		$banTypeArg = $commentarray[2];
		if ($banTypeArg >= 2) {
			$banType = 2;
		} elseif ($banTypeArg <= 0) {
			$banType = 0;
		}
	} else {
		$banType = 0;
	}
	if (isset($commentarray[3]) AND is_numeric($commentarray[3])) {
		$timeArg = $commentarray[3];
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
	if (isset($commentarray[4])) {
		$reason = str_replace($prefix.$commandName.$userName.$banTypeArg.$timeArg." ", "", $comment);;
	} else {
		$reason = "No reason specified";
	}
	switch($banType) {
		case 0:
			$query = $db->prepare("UPDATE users SET isBanned = 1, banTime = :time, banReason = :reason WHERE userID=:userID");
			$query->execute([':userID' => $userID, ':time' => $time, ':reason' => $reason]);
			$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (15, 1, :value, 1, :timestamp, :id)");
			$query->execute([':value' => $userName, ':timestamp' => $uploadDate, ':id' => $accountID]);
			break;
		case 1:
			$query = $db->prepare("UPDATE users SET isLeaderboardBanned = 1, leaderboardBanTime = :time, leaderboardBanReason = :reason WHERE userID=:userID");
			$query->execute([':userID' => $userID, ':time' => $time, ':reason' => $reason]);
			$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (15, 2, :value, 1, :timestamp, :id)");
			$query->execute([':value' => $userName, ':timestamp' => $uploadDate, ':id' => $accountID,]);
			break;
		case 2:
			$query = $db->prepare("UPDATE users SET isCreatorBanned = 1, creatorBanTime = :time, creatorBanReason = :reason WHERE userID=:userID");
			$query->execute([':userID' => $userID, ':time' => $time, ':reason' => $reason]);
			$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (15, 3, :value, 1, :timestamp, :id)");
			$query->execute([':value' => $userName, ':timestamp' => $uploadDate, ':id' => $accountID]);
			break;
	}
	return true;
}
?>