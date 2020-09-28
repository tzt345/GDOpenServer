<?php
include "../incl/lib/connection.php";
if (isset($commentarray[1])) {
	$timestamp = $commentarray[1];
} else {
	return false;
}
$query = $db->prepare("SELECT count(*) FROM actions WHERE value = :levelID AND type = 3 AND timestamp >= :timestamp");
$query->execute([':levelID' => $levelID, ':timestamp' => $timestamp]);
$count = $query->fetchColumn();

$query = $db->prepare("UPDATE levels SET likes = likes + :count WHERE levelID = :levelID");
$query->execute([':levelID' => $levelID, ':count' => $count]);
$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (19, :levelID, 1, :now, :account)");
$query->execute([':levelID' => $levelID, ':timestamp' => $timestamp, ':now' => time(), ':account' => $accountID]);

if ($query->rowCount() != 0) {
    exit("temp_0_Reverting likes successful.");
} else {
    return false;
}
?>