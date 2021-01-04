<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/GJPCheck.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
$accountID = $ep->remove($_POST["accountID"]);
$gjp = $ep->remove($_POST["gjp"]);
$targetAccountID = $ep->remove($_POST["targetAccountID"]);
// REMOVING FOR USER 1
$query = $db->prepare("DELETE FROM blocks WHERE person1 = :accountID AND person2 = :targetAccountID");
//EXECUTING THE QUERIES
$GJPCheck = new GJPCheck();
$gjpresult = $GJPCheck->check($gjp, $accountID);
if($gjpresult == 1) {
	$query->execute([':accountID' => $accountID, ':targetAccountID' => $targetAccountID]);
	echo "1";
} else {
	echo "-1";
}
?>