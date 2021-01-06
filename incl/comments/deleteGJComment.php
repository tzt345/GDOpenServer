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
$query = $db->prepare("DELETE FROM comments WHERE commentID = :commentID AND userID = :userID LIMIT 1");
$query->execute([':commentID' => $commentID, ':userID' => $userID]);
if ($query->rowCount() == 0) {
	$query = $db->prepare("SELECT levelID FROM comments WHERE commentID = :commentID");
	$query->execute([':commentID' => $commentID]);
	$levelID = $query->fetchColumn();
	$query = $db->prepare("SELECT userID FROM levels WHERE levelID = :levelID");
	$query->execute([':levelID' => $levelID]);
	$creatorID = $query->fetchColumn();
	$creatorAccID = $gs->getExtID($creatorID);
	if ($creatorAccID == $accountID OR $gs->checkPermission($accountID, "deleteComments")) {
		$query = $db->prepare("DELETE FROM comments WHERE commentID = :commentID AND levelID = :levelID LIMIT 1");
		$query->execute([':commentID' => $commentID, ':levelID' => $levelID]);
	} else {
		echo "-1";
	}
}
echo "1";
?>