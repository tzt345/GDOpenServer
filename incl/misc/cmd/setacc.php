<?php
function setacc($commentarray, $uploadDate, $accountID, $levelID) {
	include dirname(__FILE__)."/../../lib/connection.php";
	if (isset($commentarray[1])) {
		$userName = $commentarray[1];
	} else {
		return false;
	}
	$query = $db->prepare("SELECT accountID FROM accounts WHERE userName = :userName OR accountID = :userName LIMIT 1");
	$query->execute([':userName' => $userName]);
	if($query->rowCount() == 0){
		return false;
	}
	$targetAcc = $query->fetchColumn();
	$query = $db->prepare("SELECT userID FROM users WHERE extID = :extID LIMIT 1");
	$query->execute([':extID' => $targetAcc]);
	$userID = $query->fetchColumn();
	$query = $db->prepare("UPDATE levels SET extID=:extID, userID=:userID, userName=:userName WHERE levelID=:levelID");
	$query->execute([':extID' => $targetAcc, ':userID' => $userID, ':userName' => $userName, ':levelID' => $levelID]);
	$query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES (7, :value, :levelID, :timestamp, :id)");
	$query->execute([':value' => $userName, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
	return true;
}
?>