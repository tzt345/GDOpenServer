<?php
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
if ($time == 0) {
	$commentBanType = 2;
} else {
	$commentBanType = 1;
}
$query = $db->prepare("UPDATE users SET isCommentBanned = :type, commentBanTime = :time, commentBanReason = :reason WHERE userID=:userID");
$query->execute([':userID' => $userID, ':type' => $commentBanType, ':time' => $time, ':reason' => $reason]);
$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (15, 4, :value, :value2, :timestamp, :id)");
$query->execute([':value' => $userName, ':value2' => $commentBanType, ':timestamp' => $uploadDate, ':id' => $accountID]);
exit("temp_0_Commant banned $userName for $reason.");
?>