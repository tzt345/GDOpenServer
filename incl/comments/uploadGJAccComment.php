<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
require_once "../misc/commands.php";
$cmds = new Commands();
$gjp = $ep->remove($_POST["gjp"]);
$userName = $ep->remove($_POST["userName"]);
$comment = $ep->remove($_POST["comment"]);
$id = $ep->remove($_POST["accountID"]);
$userID = $gs->getUserID($id, $userName);
$uploadDate = time();
//usercheck
if ($id != "" AND $comment != "" AND $GJPCheck->check($gjp, $id) == 1) {
	$banCheck = $db->prepare("SELECT isCommentBanned, commentBanTime, commentBanReason FROM users WHERE userID = :userID");
	$banCheck->execute([':userID' => $userID]);
	$result = $banCheck->fetch();
	if ($result["isCommentBanned"] == 1) {
		if (($result["commentBanTime"] - $uploadDate) <= 0 AND $result["commentBanTime"] != 0) {
			$banExpired = $db->prepare("UPDATE users SET isCommentBanned = 0, commentBanTime = '', commentBanReason = '' WHERE userID = :userID");
			$banExpired->execute([':userID' => $userID]);
			$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (15, 4, :value, 0, :timestamp, :id)");
			$query->execute([':value' => $userName, ':timestamp' => $uploadDate, ':id' => $botAID]);
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
	} elseif ($result["isCommentBanned"] == 2) {
		if ($gameVersion >= 21) {
			if (isset($result["commentBanReason"])) {
				$reason = $result["commentBanReason"];
			} else {
				$reason = "No reason specified";
			}
			exit("temp_0_" . $reason);
		} else {
			exit("-1");
		}
	}
	$decodecomment = base64_decode($comment);
	if ($cmds->doProfileCommands($id, $decodecomment)) {
		exit("-1");
	}
	$query = $db->prepare("INSERT INTO acccomments (userName, comment, userID, timeStamp) VALUES (:userName, :comment, :userID, :uploadDate)");
	$query->execute([':userName' => $userName, ':comment' => $comment, ':userID' => $userID, ':uploadDate' => $uploadDate]);
	echo "1";
} else {
	echo "-1";
}
?>