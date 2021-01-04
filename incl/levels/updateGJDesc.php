<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$mainLib = new mainLib();
//here im getting all the data
$levelDesc = $ep->remove($_POST["levelDesc"]);
if (isset($_POST["levelID"]) AND is_numeric($_POST["levelID"])) {
	$levelID = $ep->remove($_POST["levelID"]);
} else {
	exit("-1");
}
if (isset($_POST["udid"])) {
	$id = $ep->remove($_POST["udid"]);
	if (is_numeric($id)) {
		exit("-1");
	}
} else {
	$id = $ep->remove($_POST["accountID"]);
	$gjp = $ep->remove($_POST["gjp"]);
	$gjpresult = $GJPCheck->check($gjp, $id);
	if ($gjpresult != 1) {
		exit("-1");
	}
}
$userID = $mainLib->getUserID($id, $userName);
//query
$query = $db->prepare("UPDATE levels SET levelDesc = :levelDesc WHERE levelID = :levelID AND userID = :userID");
$query->execute([':levelID' => $levelID, ':userID' => $userID, ':levelDesc' => $levelDesc]);
echo "1";
?>