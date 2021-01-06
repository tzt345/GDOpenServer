<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
$accountID = $ep->remove($_POST["accountID"]);
$gjp = $ep->remove($_POST["gjp"]);
$gjpresult = $GJPCheck->check($gjp, $accountID);
if ($gjpresult != 1) {
	exit("-1");
}
$levelID = $ep->remove($_POST["levelID"]);
if (file_exists("../../data/levels/$levelID") AND is_numeric($levelID)) {
	rename("../../data/levels/$levelID", "../../data/levels/deleted/$levelID");
	$userID = $gs->getUserID($accountID);
	$query = $db->prepare("DELETE from levels WHERE levelID = :levelID AND userID = :userID AND starStars = 0 LIMIT 1");
	$query->execute([':levelID' => $levelID, ':userID' => $userID]);
	$query6 = $db->prepare("INSERT INTO actions (type, value, timestamp, value2) VALUES (8, :itemID, :time, :ip)");
	$query6->execute([':itemID' => $levelID, ':time' => time(), ':ip' => $userID]);
	echo "1";
} else {
	echo "-1";
}
?>