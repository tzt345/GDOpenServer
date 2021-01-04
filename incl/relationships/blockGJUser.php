<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/GJPCheck.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
//here im getting all the data
if (isset($_POST["accountID"]) AND isset($_POST["gjp"]) AND isset($_POST["targetAccountID"])) {
	$accountID = $ep->remove($_POST["accountID"]);
	$gjp = $ep->remove($_POST["gjp"]);
	$targetAccountID = $ep->remove($_POST["targetAccountID"]);
	//GJPCheck
	$GJPCheck = new GJPCheck();
	$gjpresult = $GJPCheck->check($gjp, $accountID);
	if ($gjpresult == 1) {
		$query = $db->prepare("INSERT INTO blocks (person1, person2) VALUES (:accountID, :targetAccountID)");
		$query->execute([':accountID' => $accountID, ':targetAccountID' => $targetAccountID]);
		echo "1";
	} else {
		echo "-1";
	}
} else {
	echo "-1";
}
?>