<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
if (!isset($_POST["gjp"]) OR !isset($_POST["accountID"]) OR !isset($_POST["stars"]) OR !isset($_POST["levelID"])) {
	exit("-1");
}
$gjp = $ep->remove($_POST["gjp"]);
$accountID = $ep->remove($_POST["accountID"]);
$gjpresult = $GJPCheck->check($gjp, $accountID);
if ($gjpresult != 1 OR $gs->checkPermission($accountID, "actionRateStars") != 1) {
	exit("-1");
}
$stars = $ep->remove($_POST["stars"]);
$levelID = $ep->remove($_POST["levelID"]);
$difficulty = $gs->getDiffFromStars($stars);
$gs->rateLevel($accountID, $levelID, 0, $difficulty["diff"], $difficulty["auto"], $difficulty["demon"]);
echo "1";
?>