<?php
chdir(dirname(__FILE__));
//error_reporting(E_ALL);
include "../lib/connection.php";
require_once "../lib/mainLib.php";
$mainLib = new mainLib();
require_once "../lib/XORCipher.php";
require_once "../lib/GJPCheck.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../misc/commands.php";
$cmds = new Commands();
include "../../config/users.php";
$gjp = $ep->remove($_POST["gjp"]);
$userName = $ep->remove($_POST["userName"]);
$comment = $ep->remove($_POST["comment"]);
$gameVersion = $_POST["gameVersion"];
if($gameVersion < 20){
	$comment = base64_encode($comment);
}
$levelID = $ep->remove($_POST["levelID"]);
if(!empty($_POST["percent"])){
	$percent = $ep->remove($_POST["percent"]);
}else{
	$percent = 0;
}
if(!empty($_POST["accountID"]) AND $_POST["accountID"]!="0"){
	$id = $ep->remove($_POST["accountID"]);
	$register = 1;
	$GJPCheck = new GJPCheck();
	$gjpresult = $GJPCheck->check($gjp, $id);
	if($gjpresult == 0){
		exit("-1");
	}
}else{
	$id = $ep->remove($_POST["udid"]);
	$register = 0;
	if(is_numeric($id)){
		exit("-1");
	}
}
$userID = $mainLib->getUserID($id, $userName);
$uploadDate = time();
$decodecomment = base64_decode($comment);
$cmds->doCommands($id, $decodecomment, $levelID);
if($id != "" AND $comment != ""){
	$banCheck = $db->prepare("SELECT isCommentBanned, commentBanTime, commentBanReason FROM users WHERE userID = :userID");
	$banCheck->execute([':userID' => $userID]);
	$result = $banCheck->fetch(); 
	if ($result["isCommentBanned"] == 1) {
		if ($result["commentBanTime"] - time() <= 0 AND $result["commentBanTime"] != 0) {
			$banExpired = $db->prepare("UPDATE users SET isCommentBanned=0, commentBanTime=NULL, commentBanReason=NULL WHERE userID = :userID");
			$banExpired->execute([':userID' => $userID]);
			$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (15, 4, :value, 0, :timestamp, :id)");
			$query->execute([':value' => $userName, ':timestamp' => $uploadDate, ':id' => $botAID]);
		} else {
			if ($gameVersion >= 21) {
				if (isset($result["commentBanTime"])) {
					$time = $result["commentBanTime"];
				} else {
					$time = 0;
				}
				if (isset($result["commentBanReason"])) {
					$reason = $result["commentBanReason"];
				} else {
					$reason = "No reason specified";
				}
				exit("temp_".$time."_".$reason);
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
			exit("temp_0_".$reason);
		} else {
			exit("-1");
		}
	}
	$query = $db->prepare("INSERT INTO comments (userName, comment, levelID, userID, timeStamp, percent) VALUES (:userName, :comment, :levelID, :userID, :uploadDate, :percent)");
	if($register == 1){
		$query->execute([':userName' => $userName, ':comment' => $comment, ':levelID' => $levelID, ':userID' => $userID, ':uploadDate' => $uploadDate, ':percent' => $percent]);
		echo 1;
		if($percent != 0){
			$query2 = $db->prepare("SELECT percent FROM levelscores WHERE accountID = :accountID AND levelID = :levelID");
			$query2->execute([':accountID' => $id, ':levelID' => $levelID]);
			$result = $query2->fetchColumn();
			if ($query2->rowCount() == 0) {
				$query = $db->prepare("INSERT INTO levelscores (accountID, levelID, percent, uploadDate) VALUES (:accountID, :levelID, :percent, :uploadDate)");
			} else {
				if($result < $percent){
					$query = $db->prepare("UPDATE levelscores SET percent=:percent, uploadDate=:uploadDate WHERE accountID=:accountID AND levelID=:levelID");
					$query->execute([':accountID' => $id, ':levelID' => $levelID, ':percent' => $percent, ':uploadDate' => $uploadDate]);
				}
			}
		}
	}else{
		$query->execute([':userName' => $userName, ':comment' => $comment, ':levelID' => $levelID, ':userID' => $userID, ':uploadDate' => $uploadDate, ':percent' => $percent]);
		echo 1;
	}
}else{
	echo -1;
}
?>
