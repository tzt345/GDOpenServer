<?php
chdir(__DIR__);
require "../lib/connection.php";
require "../../config/users.php";
require_once "../lib/mainLib.php";
$gs = new mainLib();
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../misc/commands.php";
$cmds = new Commands();
if (!empty($_POST["accountID"])) {
	$id = $ep->remove($_POST["accountID"]);
	$gjp = $ep->remove($_POST["gjp"]);
	$gjpresult = $GJPCheck->check($gjp, $id);
	if ($gjpresult != 1) {
		exit("-1");
	}
	$register = 1;
} elseif (!empty($_POST["udid"]) AND !is_numeric($_POST["udid"])) {
	$id = $ep->remove($_POST["udid"]);
	$register = 0;
} else {
	exit("-1");
}
if (empty($_POST["comment"])) {
	exit("-1");
}
$comment = $ep->remove($_POST["comment"]);
$userName = $ep->remove($_POST["userName"]);
$gameVersion = 2;
if (!empty($_POST["gameVersion"])) {
	$gameVersion = $ep->remove($_POST["gameVersion"]);
}
if ($gameVersion < 20) {
	$comment = base64_encode($comment);
}
$levelID = $ep->remove($_POST["levelID"]);
if (!empty($_POST["percent"])) {
	$percent = $ep->remove($_POST["percent"]);
} else {
	$percent = 0;
}
$userID = $gs->getUserID($id, $userName);
$uploadDate = time();
$banCheck = $db->prepare("SELECT isCommentBanned, commentBanTime, commentBanReason FROM users WHERE userID = :userID");
$banCheck->execute([':userID' => $userID]);
$result = $banCheck->fetch();
if ($result["isCommentBanned"] == 1) {
	if (($result["commentBanTime"] - $uploadDate) <= 0 AND $result["commentBanTime"] != 0) {
		$banExpired = $db->prepare("UPDATE users SET isCommentBanned = 0, commentBanTime = '', commentBanReason = '' WHERE userID = :userID");
		$banExpired->execute([':userID' => $userID]);
		$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, value4, timestamp, account) VALUES (15, 4, :value, 0, 'Auto-unban: Ban expired', :timestamp, :id)");
		$query->execute([':value' => $userName, ':timestamp' => $uploadDate, ':id' => $gs->getBotAccountID()]);
	} else {
		if ($gameVersion >= 21) {
			if (isset($result["commentBanTime"])) {
				$time = $result["commentBanTime"] - $uploadDate;
			} else {
				$time = 0;
			}
			if (isset($result["commentBanReason"])) {
				$reason = $result["commentBanReason"];
			} else {
				$reason = "No reason specified";
			}
			exit("temp_" . $time . "_" . $reason);
		} else {
			exit("-1");
		}
	}
}
if ($gameVersion < 20) {
	$decodecomment = base64_decode($comment);
} else {
	$decodecomment = $comment;
}
$cmds->doCommands($id, $decodecomment, $levelID);
$query = $db->prepare("INSERT INTO comments (userName, comment, levelID, userID, timeStamp, percent) VALUES (:userName, :comment, :levelID, :userID, :uploadDate, :percent)");
$query->execute([':userName' => $userName, ':comment' => $comment, ':levelID' => $levelID, ':userID' => $userID, ':uploadDate' => $uploadDate, ':percent' => $percent]);
if ($register == 1) {
	if ($percent > 0 AND $percent <= 100) {
		$query2 = $db->prepare("SELECT percent FROM levelscores WHERE accountID = :accountID AND levelID = :levelID");
		$query2->execute([':accountID' => $id, ':levelID' => $levelID]);
		if ($query2->rowCount() == 0) {
			$query = $db->prepare("INSERT INTO levelscores (accountID, levelID, percent, uploadDate) VALUES (:accountID, :levelID, :percent, :uploadDate)");
			$query->execute([':accountID' => $id, ':levelID' => $levelID, ':percent' => $percent, ':uploadDate' => $uploadDate]);
		} else {
			$result = $query2->fetchColumn();
			if ($result > 100) {
				$query = $db->prepare("UPDATE users SET isLeaderboardBanned = 1, leaderboardBanTime = 0, leaderboardBanReason = 'Auto-Ban: Invalid percentage in level :levelID' WHERE extID = :accountID");
				$query->execute([':accountID' => $id, ':levelID' => $levelID]);
			} elseif ($result < $percent) {
				$query = $db->prepare("UPDATE levelscores SET percent = :percent, uploadDate = :uploadDate WHERE accountID = :accountID AND levelID = :levelID");
				$query->execute([':accountID' => $id, ':levelID' => $levelID, ':percent' => $percent, ':uploadDate' => $uploadDate]);
			}
		}
	} else {
		$query = $db->prepare("UPDATE users SET isLeaderboardBanned = 1, leaderboardBanTime = 0, leaderboardBanReason = 'Auto-Ban: Invalid percentage in level :levelID' WHERE extID = :accountID");
		$query->execute([':accountID' => $id, ':levelID' => $levelID]);
	}
}
echo "1";
?>
