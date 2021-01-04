<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
if (empty($_POST["accountID"]) OR empty($_POST["gjp"]) OR empty($_POST["requestID"])) {
	exit("-1");
}
$accountID = $ep->remove($_POST["accountID"]);
$gjp = $ep->remove($_POST["gjp"]);
$gjpresult = $GJPCheck->check($gjp, $accountID);
if ($gjpresult != 1) {
	exit("-1");
}
$requestID = $ep->remove($_POST["requestID"]);
$query = $db->prepare("UPDATE friendreqs SET isNew = 0 WHERE ID = :requestID AND toAccountID = :targetAcc");
$query->execute([':requestID' => $requestID, ':targetAcc' => $accountID]);
echo "1";
?>