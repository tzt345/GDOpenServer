<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
if (empty($_POST["gjp"]) OR empty($_POST["accountID"]) OR empty($_POST["targetAccountID"])) {
	exit("-1");
}
$accountID = $ep->remove($_POST["accountID"]);
$gjp = $ep->remove($_POST["gjp"]);
$gjpresult = $GJPCheck->check($gjp, $accountID);
if ($gjpresult != 1) {
	exit("-1");
}
$targetAccountID = $ep->remove($_POST["targetAccountID"]);
// Removing block
$query = $db->prepare("DELETE FROM blocks WHERE person1 = :accountID AND person2 = :targetAccountID");
$query->execute([':accountID' => $accountID, ':targetAccountID' => $targetAccountID]);
echo "1";
?>