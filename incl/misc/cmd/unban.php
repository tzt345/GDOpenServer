<?php
$commandName = $commentarray[0];
if (isset($commentarray[1])) {
    $userName = $commentarray[1];
} else {
    exit("temp_0_Error: No input given for required argument 'User'.");
}
if (isset($commentarray[2]) AND is_numeric($commentarray[2])) {
	$banTypeArg = $commentarray[2];
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
$query = $db->prepare("SELECT userID FROM users WHERE (extID = :userName OR userName = :userName) AND isRegistered = 1 LIMIT 1");
$query->execute([':userName' => $userName]);
if ($query->rowCount() == 0) {
    exit("temp_0_Error: No user found with the name or account ID '$userName'.");
}
$userID = $query->fetchColumn();
switch ($banType) {
    case 0:
        $query = $db->prepare("UPDATE users SET isBanned = 0, banTime = NULL, banReason = NULL WHERE userID=:userID");
        $query->execute([':userID' => $userID]);
        $query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (15, 1, :value, 0, :timestamp, :id)");
        $query->execute([':value' => $userID, ':timestamp' => $uploadDate, ':id' => $accountID]);
        $banResponse = "unbanned";
        break;
    case 1:
        $query = $db->prepare("UPDATE users SET isLeaderboardBanned = 0, leaderboardBanTime = NULL, leaderboardBanReason = NULL WHERE userID=:userID");
        $query->execute([':userID' => $userID]);
        $query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (15, 2, :value, 0, :timestamp, :id)");
        $query->execute([':value' => $userID, ':timestamp' => $uploadDate, ':id' => $accountID]);
        $banResponse = "leaderboard unbanned";
        break;
    case 2:
        $query = $db->prepare("UPDATE users SET isCreatorBanned = 0, creatorBanTime = NULL, creatorBanReason = NULL WHERE userID=:userID");
        $query->execute([':userID' => $userID]);
        $query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (15, 3, :value, 0, :timestamp, :id)");
        $query->execute([':value' => $userID, ':timestamp' => $uploadDate, ':id' => $accountID]);
        $banResponse = "creator unbanned";
        break;
}
exit("temp_0_$userName is now $banResponse.");
?>