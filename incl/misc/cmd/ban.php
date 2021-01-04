<?php
if (isset($commentArray[1])) {
	$userName = $commentArray[1];
} else {
	exit("temp_0_Error: No input given for required argument 'User'.");
}
if (isset($commentArray[2]) AND is_numeric($commentArray[2])) {
	$banTypeArg = $commentArray[2];
	if ($banTypeArg >= 2) {
		$banType = 2;
	} elseif ($banTypeArg <= 0) {
		$banType = 0;
	} else {
		$banType = 1;
	}
} else {
	$banType = 0;
}
if (isset($commentArray[3])) {
	$timeArg = $commentArray[3];
	$timeSuffix = substr($timeArg, -1);
	switch ($timeSuffix) {
		case "m":
			try {
				$time = int(trim($timeArg, "m")) * 60;
			} catch (Exception $e) {
				exit("temp_0_Error: Invalid input for argument 'Time'.");
			}
			break;
		case "h":
			try {
				$time = int(trim($timeArg, "h")) * 3600;
			} catch (Exception $e) {
				exit("temp_0_Error: Invalid input for argument 'Time'.");
			}
			break;
		case "d":
			try {
				$time = int(trim($timeArg, "d")) * 86400;
			} catch (Exception $e) {
				exit("temp_0_Error: Invalid input for argument 'Time'.");
			}
			break;
		default:
			try {
				$time = int($timeArg);
			} catch (Exception $e) {
				exit("temp_0_Error: Invalid input for argument 'Time'.");
			}
			break;
	}
	if ($time > 0) {
		$time = $uploadDate + $time;
	} elseif ($time < 0) {
		exit("temp_0_Error: Invalid input for argument 'Time'.");
	}
} else {
	$time = 0;
}
if (isset($commentArray[4])) {
	$reason = str_replace($prefix . $commentArray[0] . $userName . $banTypeArg . $timeArg . " ", "", $comment);
} else {
	$reason = "No reason specified";
}
$query = $db->prepare("SELECT userID FROM accounts WHERE accountID = :userName OR userName = :userName LIMIT 1");
$query->execute([':userName' => $userName]);
if ($query->rowCount() == 0) {
    exit("temp_0_Error: No account found with the name or account ID '$userName'.");
}
$userID = $query->fetchColumn();
switch ($banType) {
	case 0:
		$query = $db->prepare("UPDATE users SET isBanned = 1, banTime = :time, banReason = :reason WHERE userID = :userID");
		$query->execute([':userID' => $userID, ':time' => $time, ':reason' => $reason]);
		$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, value4, timestamp, account) VALUES (15, 1, :value, 1, :reason, :timestamp, :id)");
		$query->execute([':value' => $userID, ':reason' => $reason, ':timestamp' => $uploadDate, ':id' => $accountID]);
		$banResponse = "Banned";
		break;
	case 1:
		$query = $db->prepare("UPDATE users SET isLeaderboardBanned = 1, leaderboardBanTime = :time, leaderboardBanReason = :reason WHERE userID = :userID");
		$query->execute([':userID' => $userID, ':time' => $time, ':reason' => $reason]);
		$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, value4, timestamp, account) VALUES (15, 2, :value, 1, :reason, :timestamp, :id)");
		$query->execute([':value' => $userID, ':reason' => $reason, ':timestamp' => $uploadDate, ':id' => $accountID]);
		$banResponse = "Leaderboard banned";
		break;
	case 2:
		$query = $db->prepare("UPDATE users SET isCreatorBanned = 1, creatorBanTime = :time, creatorBanReason = :reason WHERE userID = :userID");
		$query->execute([':userID' => $userID, ':time' => $time, ':reason' => $reason]);
		$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, value4, timestamp, account) VALUES (15, 3, :value, 1, :reason, :timestamp, :id)");
		$query->execute([':value' => $userID, ':reason' => $reason, ':timestamp' => $uploadDate, ':id' => $accountID]);
		$banResponse = "Creator banned";
		break;
}
if ($time != 0) {
	$timeResponse = "temporarily until " . date("d/m/Y", $time);
} else {
	$timeResponse = "permanently";
}
exit("temp_0_$banResponse $userName $timeResponse for '$reason'.");
?>