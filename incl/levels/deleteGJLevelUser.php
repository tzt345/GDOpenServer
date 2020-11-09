<?php
chdir(__DIR__);
include "../lib/connection.php";
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$mainLib = new mainLib();
$levelID = $ep->remove($_POST["levelID"]);
$accountID = $ep->remove($_POST["accountID"]);
$gjp = $ep->remove($_POST["gjp"]);
$gjpresult = $GJPCheck->check($gjp, $accountID);
if(!is_numeric($levelID)){
	exit("-1");
}
if($gjpresult == 1){
	$userID = $mainLib->getUserID($accountID);
	$query = $db->prepare("DELETE from levels WHERE levelID = :levelID AND userID = :userID AND starStars = 0 LIMIT 1");
	$query->execute([':levelID' => $levelID, ':userID' => $userID]);
	$query6 = $db->prepare("INSERT INTO actions (type, value, timestamp, value2) VALUES (8, :itemID, :time, :ip)");
	$query6->execute([':itemID' => $levelID, ':time' => time(), ':ip' => $userID]);
	if(file_exists("../../data/levels/$levelID") AND $query->rowCount() != 0){
		rename("../../data/levels/$levelID", "../../data/levels/deleted/$levelID");
	} else {
		exit("-1");
	}
	echo "1";
}else{
	echo "-1";
}
?>