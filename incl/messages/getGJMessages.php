<?php
chdir(dirname(__FILE__));
//error_reporting(0);
include "../lib/connection.php";
require_once "../lib/GJPCheck.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
//code begins
$toAccountID = $ep->remove($_POST["accountID"]);
$gjp = $ep->remove($_POST["gjp"]);
$GJPCheck = new GJPCheck();
$gjpresult = $GJPCheck->check($gjp, $toAccountID);
if($gjpresult != 1){
	exit("-1");
}
$page = $ep->remove($_POST["page"]);
$offset = $page * 10;
if(!isset($_POST["getSent"]) OR $_POST["getSent"] != 1){
	$query = $db->prepare("SELECT * FROM messages WHERE toAccountID = :toAccountID AND messageID NOT NULL ORDER BY messageID DESC LIMIT 10 OFFSET $offset");
	$countquery = $db->prepare("SELECT count(*) FROM messages WHERE toAccountID = :toAccountID");
	$getSent = 0;
}else{
	$query = $db->prepare("SELECT * FROM messages WHERE accID = :toAccountID AND messageID NOT NULL ORDER BY messageID DESC LIMIT 10 OFFSET $offset");
	$countquery = $db->prepare("SELECT count(*) FROM messages WHERE accID = :toAccountID");
	$getSent = 1;
}
$countquery->execute([':toAccountID' => $toAccountID]);
$msgcount = $countquery->fetchColumn();
if($msgcount == 0){
	exit("-2");
}
$query->execute([':toAccountID' => $toAccountID]);
$result = $query->fetchAll();
$msgstring = "";
foreach ($result as &$message) {
	if($getSent == 1){
		$accountID = $message["toAccountID"];
	}else{
		$accountID = $message["accID"];
	}
	$query=$db->prepare("SELECT userName, userID, extID FROM users WHERE extID = :accountID LIMIT 1");
	$query->execute([':accountID' => $accountID]);
	$result = $query->fetchAll();
	$uploadDate = date("d/m/Y G.i", $message["timestamp"]);
	$msgstring .= "6:".$result["userName"].":3:".$result["userID"].":2:".$result["extID"].":1:".$message["messageID"].":4:".$message["subject"].":8:".$message["isNew"].":9:".$getSent.":7:".$uploadDate."|";
}
$msgstring = substr($msgstring, 0, -1);
echo $msgstring ."#".$msgcount.":".$offset.":10";
?>