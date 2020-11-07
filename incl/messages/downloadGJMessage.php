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
function timing ($time) {
    $time = time() - $time; // to get the time since that moment
    $time = ($time<1)? 1 : $time;
    $tokens = array (31536000 => 'year', 2592000 => 'month', 604800 => 'week', 86400 => 'day', 3600 => 'hour', 60 => 'minute', 1 => 'second');
    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
    }
}
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
	$uploadDate = timing($result["timestamp"]);
	echo "6:".$result2["userName"].":3:".$result2["userID"].":2:".$accountID.":1:".$messageID.":4:".$result["subject"].":8:".$result["isNew"].":9:".$isSender.":5:".$result["body"].":7:".$uploadDate."";
}else{
	echo -1;
}
?>