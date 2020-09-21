<?php
function disablesong($commentarray, $uploadDate, $accountID, $levelID) {
	include dirname(__FILE__)."/../../lib/connection.php";
	if (isset($commentarray[1]) AND is_numeric($commentarray[1])) {
		$song = $commentarray[1];
	} else {
		$query = $db->prepare("SELECT songID FROM levels WHERE levelID=:levelID");
		$query->execute([':levelID' => $levelID]);
		$song = $query->fetchColumn();
	}
	$query = $db->prepare("SELECT count(*) FROM songs WHERE ID=:song");
	$query->execute([':song' => $song]);
	if ($query->fetch() <= 0) {
		return false;
	}
	$query = $db->prepare("UPDATE songs SET isDisabled=1 WHERE ID=:song");
	$query->execute([':song' => $song]);
	$query = $db->prepare("INSERT INTO modactions (type, value, value2, timestamp, account, value3) VALUES (18, 0, :value2, :timestamp, :id, :levelID)");
	$query->execute([':value2' => $song, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
	return true;
}
?>