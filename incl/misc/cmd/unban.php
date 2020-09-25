<?php
function unban($commentarray, $uploadDate, $accountID, $levelID) {
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
	switch($banType) {
		case 0:
			$query = $db->prepare("UPDATE users SET isBanned = 0, banTime = NULL, banReason = NULL WHERE userID=:userID");
			$query->execute([':userID' => $userID]);
			$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (15, 1, :value, 0, :timestamp, :id)");
			$query->execute([':value' => $userName, ':timestamp' => $uploadDate, ':id' => $accountID]);
			break;
		case 1:
			$query = $db->prepare("UPDATE users SET isLeaderboardBanned = 0, leaderboardBanTime = NULL, leaderboardBanReason = NULL WHERE userID=:userID");
			$query->execute([':userID' => $userID]);
			$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (15, 2, :value, 0, :timestamp, :id)");
			$query->execute([':value' => $userName, ':timestamp' => $uploadDate, ':id' => $accountID]);
			break;
		case 2:
			$query = $db->prepare("UPDATE users SET isCreatorBanned = 0, creatorBanTime = NULL, creatorBanReason = NULL WHERE userID=:userID");
			$query->execute([':userID' => $userID]);
			$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (15, 3, :value, 0, :timestamp, :id)");
			$query->execute([':value' => $userName, ':timestamp' => $uploadDate, ':id' => $accountID]);
			break;
	}
	return true;
}
?>