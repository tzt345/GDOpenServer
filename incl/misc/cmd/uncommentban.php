<?php
function uncommentban($comment, $commentarray, $uploadDate, $accountID, $levelID) {
	include dirname(__FILE__)."/../../lib/connection.php";
	$commandName = $commentarray[0];
	if (isset($commentarray[1])) {
		$userName = $commentarray[1];
	} else {
		return false;
	}
	$query = $db->prepare("UPDATE users SET isCommentBanned = 0, commentBanTime = NULL, commentBanReason = NULL WHERE userID=:userID");
	$query->execute([':userID' => $userID, ':time' => $time, ':reason' => $reason]);
	$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (15, 4, :value, 0, :timestamp, :id)");
	$query->execute([':value' => $userName, ':timestamp' => $uploadDate, ':id' => $accountID]);
	return true;
}
?>