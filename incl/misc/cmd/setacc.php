<?php
if (isset($commentarray[1])) {
	$userName = $commentarray[1];
} else {
	exit("temp_0_Error: No input given for required argument 'User'.");
}
$query = $db->prepare("SELECT accountID FROM accounts WHERE userName = :userName OR accountID = :userName LIMIT 1");
$query->execute([':userName' => $userName]);
if($query->rowCount() == 0){
	exit("temp_0_Error: No user found with the name or account ID '$userName'.");
}
$targetAcc = $query->fetchColumn();
$query = $db->prepare("SELECT userID FROM users WHERE extID = :extID LIMIT 1");
$query->execute([':extID' => $targetAcc]);
$userID = $query->fetchColumn();
$query = $db->prepare("UPDATE levels SET extID = :extID, userID = :userID, userName = :userName WHERE levelID = :levelID");
$query->execute([':extID' => $targetAcc, ':userID' => $userID, ':userName' => $userName, ':levelID' => $levelID]);
$query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES (7, :value, :levelID, :timestamp, :id)");
$query->execute([':value' => $userName, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
exit("temp_0_Level ownership transferred to $userName. If this was an accident, get in contact with a moderator.");
?>