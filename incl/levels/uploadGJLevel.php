<?php
chdir(__DIR__);
require "../lib/connection.php";
require "../../config/levels.php";
require "../../config/users.php";
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
//here im getting all the data
if (!empty($_POST["accountID"])) {
	$id = $ep->remove($_POST["accountID"]);
	$register = 1;
} elseif ($unregisteredUploadLevels == 0) {
	exit("-1");
} elseif (isset($_POST["udid"]) AND !is_numeric($_POST["udid"])) {
	$id = $ep->remove($_POST["udid"]);
	$register = 0;
} else {
	exit("-1");
}
$gameVersion = 1;
if (!empty($_POST["gameVersion"])) {
	$gameVersion = $ep->remove($_POST["gameVersion"]);
}
if ($gameVersion >= 20 AND $register == 1) {
	if (empty($_POST["gjp"])) {
		exit("-1");
	}
	$gjp = $ep->remove($_POST["gjp"]);
	$gjpresult = $GJPCheck->check($gjp, $id);
	if ($gjpresult != 1) {
		exit("-1");
	}
}
$uploadDate = time();
$userName = $ep->remove($_POST["userName"]);
$userName = $ep->charclean($userName);
$hostname = $gs->getIP();
$query2 = $db->prepare("SELECT userID, isBanned, banTime, isCreatorBanned, creatorBanTime FROM users WHERE extID = :id");
$query2->execute([':id' => $id]);
if ($query2->rowCount() > 0) {
	$result = $query2->fetch();
	$userID = $result["userID"];
	if ($result["isBanned"] == 1) {
		if (($result["banTime"] - $uploadDate) <= 0 AND $result["banTime"] != 0) {
			$banExpired = $db->prepare("UPDATE users SET isBanned = 0, banTime = '', banReason = '' WHERE userID = :userID");
			$banExpired->execute([':userID' => $userID]);
			$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, value4, timestamp, account) VALUES (15, 1, :value, 0, 'Auto-unban: Ban expired', :timestamp, :id)");
			$query->execute([':value' => $userName, ':timestamp' => $time, ':id' => $gs->getBotAccountID()]);
		} else {
			exit("-1");
		}
	} elseif ($result["isCreatorBanned"] == 1) {
		if (($result["creatorBanTime"] - $uploadDate) <= 0 AND $result["creatorBanTime"] != 0) {
			$banExpired = $db->prepare("UPDATE users SET isCreatorBanned = 0, creatorBanTime = '', creatorBanReason = '' WHERE userID = :userID");
			$banExpired->execute([':userID' => $userID]);
			$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, value4, timestamp, account) VALUES (15, 3, :value, 0, 'Auto-unban: Ban expired', :timestamp, :id)");
			$query->execute([':value' => $userName, ':timestamp' => $time, ':id' => $gs->getBotAccountID()]);
		} else {
			exit("-1");
		}
	}
} else {
	$query = $db->prepare("INSERT INTO users (isRegistered, extID, userName, lastPlayed) VALUES (:register, :id, :userName, :uploadDate)");
	$query->execute([':register' => $register, ':id' => $id, ':userName' => $userName, ':uploadDate' => $uploadDate]);
	$userID = $db->lastInsertId();
}
$query = $db->prepare("SELECT count(*) FROM levels WHERE uploadDate > :time AND (userID = :userID OR hostname = :ip)");
$query->execute([':time' => $uploadDate - $uploadRateLimit, ':userID' => $userID, ':ip' => $hostname]);
if ($query->fetchColumn() > 0 OR !isset($_POST["levelID"]) OR empty($_POST["levelString"]) OR empty($_POST["levelName"]) OR empty($_POST["levelDesc"]) OR empty($_POST["levelVersion"]) OR empty($_POST["levelLength"]) OR empty($_POST["audioTrack"])) {
	exit("-1");
}
$levelID = $ep->remove($_POST["levelID"]);
$levelString = $ep->remove($_POST["levelString"]);
$levelName = $ep->remove($_POST["levelName"]);
$levelName = $ep->charclean($levelName);
$levelDesc = $ep->remove($_POST["levelDesc"]);
if ($gameVersion < 20) {
	$levelDesc = base64_encode($levelDesc);
}
$levelVersion = $ep->remove($_POST["levelVersion"]);
$levelLength = $ep->remove($_POST["levelLength"]);
$audioTrack = $ep->remove($_POST["audioTrack"]);
if (isset($_POST["binaryVersion"])) {
	$binaryVersion = $ep->remove($_POST["binaryVersion"]);	
} else {
	$binaryVersion = $gameVersion;
}
if (isset($_POST["auto"])) {
	$auto = $ep->remove($_POST["auto"]);
} else {
	$auto = 0;
}
if (isset($_POST["password"])) {
	$password = $ep->remove($_POST["password"]);
} elseif ($gameVersion > 17) {
	$password = 0;
} else {
	$password = 1;
}
if (isset($_POST["original"])) {
	$original = $ep->remove($_POST["original"]);
} else {
	$original = 0;
}
if (isset($_POST["twoPlayer"])) {
	$twoPlayer = $ep->remove($_POST["twoPlayer"]);
} else {
	$twoPlayer = 0;
}
if (isset($_POST["songID"])) {
	$songID = $ep->remove($_POST["songID"]);
} else {
	$songID = 0;
}
if (isset($_POST["objects"])) {
	$objects = $ep->remove($_POST["objects"]);
} else {
	$objects = 0;
}
if (isset($_POST["coins"])) {
	$coins = $ep->remove($_POST["coins"]);
} else {
	$coins = 0;
}
if (isset($_POST["requestedStars"])) {
	$requestedStars = $ep->remove($_POST["requestedStars"]);
} else {
	$requestedStars = 0;
}
if (isset($_POST["extraString"])) {
	$extraString = $ep->remove($_POST["extraString"]);
} else {
	$extraString = "29_29_29_40_29_29_29_29_29_29_29_29_29_29_29_29";
}
if (isset($_POST["levelInfo"])) {
	$levelInfo = $ep->remove($_POST["levelInfo"]);
} else {
	$levelInfo = 0;
}
if (isset($_POST["unlisted"])) {
	$unlisted = $ep->remove($_POST["unlisted"]);
} else {
	$unlisted = 0;
}
if (isset($_POST["ldm"])) {
	$ldm = $ep->remove($_POST["ldm"]);
} else {
	$ldm = 0;
}

if ($levelID != 0) {
	$querye = $db->prepare("SELECT levelID FROM levels WHERE levelID = :levelID AND userID = :userID LIMIT 1");
	$querye->execute([':levelID' => $levelID, ':userID' => $userID]);
	$levelID = $querye->fetchColumn();
	if ($querye->rowCount() == 1) {
		$query = $db->prepare("UPDATE levels SET levelName = :levelName, gameVersion = :gameVersion,  binaryVersion = :binaryVersion, userName = :userName, levelDesc = :levelDesc, levelVersion = :levelVersion, levelLength = :levelLength, audioTrack = :audioTrack, auto = :auto, password = :password, original = :original, twoPlayer = :twoPlayer, songID = :songID, objects = :objects, coins = :coins, requestedStars = :requestedStars, extraString = :extraString, levelString = :levelString, levelInfo = :levelInfo, updateDate = :uploadDate, unlisted = :unlisted, hostname = :hostname, isLDM = :ldm WHERE levelName = :levelName AND extID = :id");	
		$query->execute([':levelName' => $levelName, ':gameVersion' => $gameVersion, ':binaryVersion' => $binaryVersion, ':userName' => $userName, ':levelDesc' => $levelDesc, ':levelVersion' => $levelVersion, ':levelLength' => $levelLength, ':audioTrack' => $audioTrack, ':auto' => $auto, ':password' => $password, ':original' => $original, ':twoPlayer' => $twoPlayer, ':songID' => $songID, ':objects' => $objects, ':coins' => $coins, ':requestedStars' => $requestedStars, ':extraString' => $extraString, ':levelString' => "", ':levelInfo' => $levelInfo, ':levelName' => $levelName, ':id' => $id, ':uploadDate' => $uploadDate, ':unlisted' => $unlisted, ':hostname' => $hostname, ':ldm' => $ldm]);
		file_put_contents("../../data/levels/$levelID", $levelString);
		echo $levelID;
	}
} else {
	$query = $db->prepare("INSERT INTO levels (levelName, gameVersion, binaryVersion, userName, levelDesc, levelVersion, levelLength, audioTrack, auto, password, original, twoPlayer, songID, objects, coins, requestedStars, extraString, levelString, levelInfo, uploadDate, userID, extID, updateDate, unlisted, hostname, isLDM) VALUES (:levelName, :gameVersion, :binaryVersion, :userName, :levelDesc, :levelVersion, :levelLength, :audioTrack, :auto, :password, :original, :twoPlayer, :songID, :objects, :coins, :requestedStars, :extraString, :levelString, :levelInfo, :uploadDate, :userID, :id, :uploadDate, :unlisted, :hostname, :ldm)");
	$query->execute([':levelName' => $levelName, ':gameVersion' => $gameVersion, ':binaryVersion' => $binaryVersion, ':userName' => $userName, ':levelDesc' => $levelDesc, ':levelVersion' => $levelVersion, ':levelLength' => $levelLength, ':audioTrack' => $audioTrack, ':auto' => $auto, ':password' => $password, ':original' => $original, ':twoPlayer' => $twoPlayer, ':songID' => $songID, ':objects' => $objects, ':coins' => $coins, ':requestedStars' => $requestedStars, ':extraString' => $extraString, ':levelString' => "", ':levelInfo' => $levelInfo, ':uploadDate' => $uploadDate, ':userID' => $userID, ':id' => $id, ':unlisted' => $unlisted, ':hostname' => $hostname, ':ldm' => $ldm]);
	file_put_contents("../../data/levels/$levelID", $levelString);
	echo $db->lastInsertId();
}
?>
