<?php
function renamelevel($comment, $commentarray, $uploadDate, $accountID, $levelID) {
	include dirname(__FILE__)."/../../lib/connection.php";
	include dirname(__FILE__)."/../../../config/commands.php";
	$commandName = $commentarray[0];
	if (isset($commentarray[1])) {
		$name = str_replace($prefix.$commandName." ", "", $comment);
	} else {
		return false;
	}
	if (strlen($name) > 40) {
		return false;
	}
	$query = $db->prepare("UPDATE levels SET levelName=:levelName WHERE levelID=:levelID");
	$query->execute([':levelID' => $levelID, ':levelName' => $name]);
	$query = $db->prepare("INSERT INTO modactions (type, value, timestamp, account, value3) VALUES (8, :value, :timestamp, :id, :levelID)");
	$query->execute([':value' => $name, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
	return true;
}
?>