<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/mainLib.php";
$gs = new mainLib();
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
$accountID = $ep->remove($_POST["accountID"]);
$gjp = $ep->remove($_POST["gjp"]);
$gjpresult = $GJPCheck->check($gjp, $accountID);
if ($gjpresult != 1) {
	exit("-1");
}
$commentID = $ep->remove($_POST["commentID"]);
$userID = $gs->getUserID($accountID);
$query = $db->prepare("DELETE FROM acccomments WHERE commentID = :commentID AND userID = :userID LIMIT 1");
$query->execute([':commentID' => $commentID, ':userID' => $userID]);
if ($query->rowCount() == 0) {
	if ($gs->checkPermission($accountID, "deleteComments") == 1) {
		$query = $db->prepare("DELETE FROM acccomments WHERE commentID = :commentID LIMIT 1");
		$query->execute([':commentID' => $commentID]);
	} else {
		echo "-1";
	}
} else {
	echo "1";
}
?>