<?php
$commandName = $commentarray[0];
if (isset($commentarray[1])) {
	$userName = $commentarray[1];
} else {
	exit("temp_0_Error: No input given.");
}
$query = $db->prepare("SELECT userID FROM users WHERE (extID = :userName OR userName = :userName) AND isRegistered = 1 LIMIT 1");
$query->execute([':userName' => $userName]);
if ($query->rowCount() == 0) {
    exit("temp_0_Error: No user found with the name or account ID '$userName'.");
}
$userID = $query->fetchColumn();
$query = $db->prepare("UPDATE users SET isCommentBanned = 0, commentBanTime = NULL, commentBanReason = NULL WHERE userID = :userID");
$query->execute([':userID' => $userID]);
$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (15, 4, :value, 0, :timestamp, :id)");
$query->execute([':value' => $userID, ':timestamp' => $uploadDate, ':id' => $accountID]);
exit("temp_0_$userName has been unbanned from commenting.");
?>