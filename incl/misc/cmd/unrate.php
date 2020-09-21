<?php
function unrate($gs, $commentarray, $uploadDate, $accountID, $levelID) {
	include dirname(__FILE__)."/../../lib/connection.php";
	if (isset($commentarray[1]) AND is_numeric($commentarray[1])) {
		$keepDiff = $commentarray[1];
		if ($keepDiff >= 1) {
			$keepDiff = 1;
		} else {
			$keepDiff = 0;
		}
	} else {
		$keepDiff = 0;
	}
	if (isset($commentarray[2]) AND is_numeric($commentarray[2])) {
		$unfeature = $commentarray[2];
		if ($unfeature >= 1) {
			$unfeature = 1;
		} else {
			$unfeature = 0;
		}
	} else {
		$unfeature = 0;
	}
	if (isset($commentarray[3]) AND is_numeric($commentarray[3])) {
		$unverifyCoins = $commentarray[3];
		if ($unverifyCoins >= 1) {
			$unverifyCoins = 1;
		} else {
			$unverifyCoins = 0;
		}
	} else {
		$unverifyCoins = 0;
	}
	if ($keepDiff == 1) {
		$query = $db->prepare("UPDATE levels SET starStars=0, starDemon=0, starAuto=0 WHERE levelID=:levelID");
		$query->execute([':levelID' => $levelID]);
		$query = $db->prepare("SELECT starDifficulty FROM levels WHERE levelID=:levelID");
		$query->execute([':levelID' => $levelID]);
		$levelDiff = $query->fetchColumn();
		$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (1, :value, '0', :levelID, :timestamp, :id)");
		$query->execute([':value' => $gs->getDifficulty($levelDiff, 0, 0),':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
	} else {
		$query = $db->prepare("UPDATE levels SET starStars=0, starDifficulty=0, starDemon=0, starAuto=0 WHERE levelID=:levelID");
		$query->execute([':levelID' => $levelID]);
		$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (1, 'N/A', '0', :levelID, :timestamp, :id)");
		$query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
	}
	if ($unfeature == 1) {
		if ($gs->checkPermission($accountID, "commandFeature")) {
			$query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES (2, 0, :levelID, :timestamp, :id)");
			$query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);	
			$query = $db->prepare("UPDATE levels SET starFeatured='0' WHERE levelID=:levelID");
			$query->execute([':levelID' => $levelID]);
		}
	}
	if ($unverifyCoins == 1) {
		if ($gs->checkPermission($accountID, "commandVerifycoins")) {
			$query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES (3, 0, :levelID, :timestamp, :id)");
			$query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
			$query = $db->prepare("UPDATE levels SET starCoins='0' WHERE levelID=:levelID");
			$query->execute([':levelID' => $levelID]);
		}
	}
	return true;
}
?>
