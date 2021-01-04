<?php
if (isset($commentArray[1]) AND is_numeric($commentArray[1])) {
	$song = $commentArray[1];
} else {
	$query = $db->prepare("SELECT songID FROM levels WHERE levelID = :levelID");
	$query->execute([':levelID' => $levelID]);
	$song = $query->fetchColumn();
}
$query = $db->prepare("SELECT count(*) FROM songs WHERE ID=:song");
$query->execute([':song' => $song]);
if ($query->fetch() <= 0) {
	exit("temp_0_Error: Song not found.");
}
$query = $db->prepare("UPDATE songs SET isDisabled = 0 WHERE ID = :song");
$query->execute([':song' => $song]);
$query = $db->prepare("INSERT INTO modactions (type, value, value2, timestamp, account, value3) VALUES (18, 1, :value2, :timestamp, :id, :levelID)");
$query->execute([':value2' => $song, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
exit("temp_0_Song has been enabled for use.");
?>