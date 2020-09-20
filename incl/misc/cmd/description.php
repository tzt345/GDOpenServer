<?php
function description($comment, $commentarray, $uploadDate, $accountID, $levelID) {
	include dirname(__FILE__)."/../../lib/connection.php";
	include dirname(__FILE__)."/../../../config/commands.php";
	$commandName = $commentarray[0];
	if (isset($commentarray[1])) {
		$desc = base64_encode(str_replace($prefix.$commandName." ", "", $comment));
	} else {
		return false;
	}
	$query = $db->prepare("UPDATE levels SET levelDesc=:desc WHERE levelID=:levelID");
	$query->execute([':levelID' => $levelID, ':desc' => $desc]);
	$query = $db->prepare("INSERT INTO modactions (type, value, timestamp, account, value3) VALUES (13, :value, :timestamp, :id, :levelID)");
	$query->execute([':value' => $desc, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
	return true;
}
?>