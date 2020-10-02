<?php
if (isset($commentarray[1]) AND is_numeric($commentarray[1])) {
	$song = $commentarray[1];
} else {
	exit("temp_0_Error: No input given for required argument 'Song ID'.");
}
$query = $db->prepare("UPDATE levels SET songID = :song WHERE levelID = :levelID");
$query->execute([':levelID' => $levelID, ':song' => $song]);
$query = $db->prepare("INSERT INTO modactions (type, value, timestamp, account, value3) VALUES ('Level song change', :value, :timestamp, :id, :levelID)");
$query->execute([':value' => $song, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
exit("temp_0_Song ID changed to $song. Re-download the level to see the changes.");
?>