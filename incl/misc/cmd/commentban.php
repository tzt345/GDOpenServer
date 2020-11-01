<?php
if (isset($commentarray[1])) {
	$userName = $commentarray[1];
} else {
	exit("temp_0_Error: No input given for required argument 'User'.");
}
if (isset($commentarray[2])) {
	$timeArg = $commentarray[2];
	$timeSuffix = substr($timeArg, -1);
	switch($timeSuffix) {
		case "m":
			try {
				$time = int(trim($timeArg, "m")) * 60;
			} catch (Exception $e) {
				exit("temp_0_Error: Invalid input for the duration of the ban.");
			}
			break;
		case "h":
			try {
				$time = int(trim($timeArg, "h")) * 3600;
			} catch (Exception $e) {
				exit("temp_0_Error: Invalid input for the duration of the ban.");
			}
			break;
		case "d":
			try {
				$time = int(trim($timeArg, "d")) * 86400;
			} catch (Exception $e) {
				exit("temp_0_Error: Invalid input for the duration of the ban.");
			}
			break;
		default:
			try {
				$time = $uploadDate + int($timeArg);
			} catch (Exception $e) {
				exit("temp_0_Error: Invalid input for the duration of the ban.");
			}
			break;
	}
	$commentBanType = 2;
	if ($time > 0) {
		$time = $uploadDate + $time;
		$commentBanType = 1;
	} elseif ($time < 0) {
		exit("temp_0_Error: Invalid input for the duration of the ban.");
	}
} else {
	$time = 0;
}
if (isset($commentarray[3])) {
	$reason = str_replace($prefix.$commentarray[0].$userName.$banTypeArg.$timeArg." ", "", $comment);
} else {
	$reason = "No reason specified";
}
$query = $db->prepare("SELECT userID FROM users WHERE (extID = :userName OR userName = :userName) AND isRegistered = 1 LIMIT 1");
$query->execute([':userName' => $userName]);
if ($query->rowCount() == 0) {
    exit("temp_0_Error: No user found with the name or account ID '$userName'.");
}
$userID = $query->fetchColumn();
$query = $db->prepare("UPDATE users SET isCommentBanned = :type, commentBanTime = :time, commentBanReason = :reason WHERE userID = :userID");
$query->execute([':userID' => $userID, ':type' => $commentBanType, ':time' => $time, ':reason' => $reason]);
$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (15, 4, :value, :value2, :timestamp, :id)");
$query->execute([':value' => $userName, ':value2' => $commentBanType, ':timestamp' => $uploadDate, ':id' => $accountID]);
if ($time != 0) {
	$timeResponse = "temporarily until ".date("d/m/Y", $time);
} else {
	$timeResponse = "permanently";
}
exit("temp_0_Comment banned $userName $timeResponse for '$reason'.");
?>
