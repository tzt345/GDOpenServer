<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
//here im getting all the data
if (empty($_POST["accountID"]) AND empty($_POST["gjp"]) AND empty($_POST["targetAccountID"])) {
	exit("-1");
}
$gjp = $ep->remove($_POST["gjp"]);
$accountID = $ep->remove($_POST["accountID"]);
$gjpresult = $GJPCheck->check($gjp, $accountID); //GJPCheck
if ($gjpresult != 1) {
	exit("-1");
}
$targetAccountID = $ep->remove($_POST["targetAccountID"]);
$query = $db->prepare("INSERT INTO blocks (person1, person2) VALUES (:accountID, :targetAccountID)");
$query->execute([':accountID' => $accountID, ':targetAccountID' => $targetAccountID]);
echo "1";
?>