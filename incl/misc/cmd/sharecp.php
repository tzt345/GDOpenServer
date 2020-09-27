<?php
include dirname(__FILE__) . "/../../lib/connection.php";
if (isset($commentarray[1])) {
    $userName = $commentarray[1];
} else {
    return false;
}
$query = $db->prepare("SELECT userID, isCreatorBanned FROM users WHERE extID = :userName OR userName = :userName ORDER BY isRegistered DESC LIMIT 1");
$query->execute([':userName' => $userName]);
$result = $query->fetch();
if ($query->rowCount() == 0 OR $targetExtID == $accountID OR $result["isCreatorBanned"] == 1) {
    return false;
}
$userID = $result["userID"];
$query = $db->prepare("INSERT INTO cpshares (levelID, userID) VALUES (:levelID, :userID)");
$query->execute([':userID' => $userID, ':levelID' => $levelID]);
$query = $db->prepare("UPDATE levels SET isCPShared=1 WHERE levelID=:levelID");
$query->execute([':levelID' => $levelID]);
$query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES (11, :value, :levelID, :timestamp, :id)");
$query->execute([':value' => $userName, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
exit("temp_0_Creator Points awareded on this level are now shared with $userName.");
?>