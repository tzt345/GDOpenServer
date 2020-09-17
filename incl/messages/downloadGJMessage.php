<?php
chdir(dirname(__FILE__));
include "../lib/connection.php";
require_once "../lib/GJPCheck.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
$accountID = $ep->remove($_POST["accountID"]);
$gjp = $ep->remove($_POST["gjp"]);
$GJPCheck = new GJPCheck();
$gjpresult = $GJPCheck->check($gjp, $accountID);
if($gjpresult == 1){
	$messageID = $ep->remove($_POST["messageID"]);
	$query = $db->prepare("SELECT accID, toAccountID, timestamp, userName, subject, isNew, body FROM messages WHERE messageID = :messageID AND (accID = :accID OR toAccountID = :accID) LIMIT 1");
	$query->execute([':messageID' => $messageID, ':accID' => $accountID]);
	$result = $query->fetch();
	if($query->rowCount() == 0){
		exit("-1");
	}
	$isSender = $ep->remove($_POST["isSender"]);
	if ($isSender != 1) {
		$query = $db->prepare("UPDATE messages SET isNew=1 WHERE messageID = :messageID AND toAccountID = :accID");
		$query->execute([':messageID' => $messageID, ':accID' => $accountID]);
		$accountID = $result["accID"];
		$isSender = 0;
	} else {
		$accountID = $result["toAccountID"];
	}
	$query = $db->prepare("SELECT userName, userID FROM users WHERE extID = :accountID");
	$query->execute([':accountID' => $accountID]);
	$result2 = $query->fetch();
	$uploadDate = date("d/m/Y G.i", $result["timestamp"]);
	echo "6:".$result2["userName"].":3:".$result2["userID"].":2:".$accountID.":1:".$messageID.":4:".$result["subject"].":8:".$result["isNew"].":9:".$isSender.":5:".$result["body"].":7:".$uploadDate."";
}else{
	echo -1;
}
?>