<?php
chdir(__DIR__);
//error_reporting(0);
include "../lib/connection.php";
require_once "../lib/GJPCheck.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
$gjp = $ep->remove($_POST["gjp"]);
$accountID = $ep->remove($_POST["accountID"]);
$GJPCheck = new GJPCheck();
$gjpresult = $GJPCheck->check($gjp, $accountID);
if ($gjpresult != 1) {
	exit("-1");
}
$subject = $ep->remove($_POST["subject"]);
$toAccountID = $ep->number($_POST["toAccountID"]);
$body = $ep->remove($_POST["body"]);

$query2 = $db->prepare("SELECT count(*) FROM blocks WHERE person1 = :toAccountID AND person2 = :accountID LIMIT 1");
$query3 = $db->prepare("SELECT mS FROM accounts WHERE accountID = :toAccountID AND mS != 0 LIMIT 1");
$query4 = $db->prepare("SELECT count(*) FROM friendships WHERE (person1 = :accountID AND person2 = :toAccountID) AND (person2 = :accountID AND person1 = :toAccountID) LIMIT 1");
$query2->execute([':toAccountID' => $toAccountID, ':accountID' => $accountID]);
$query3->execute([':toAccountID' => $toAccountID]);
$query4->execute([':toAccountID' => $toAccountID, ':accountID' => $accountID]);
$blocked = $query2->fetchColumn();
$mSOnly = $query3->fetchColumn();
$friend = $query4->fetchColumn();

if ($blocked == 1 OR $mSOnly == 2) {
	exit("-1");
} elseif (($friend == 1 AND $mSOnly == 1) OR $mSOnly == 0) {
	$query = $db->prepare("SELECT userName FROM users WHERE extID = :accountID LIMIT 1");
	$query->execute([':accountID' => $accountID]);
	$userName = $query->fetchColumn();
	$userID = $gs->getUserID($accountID);
	$uploadDate = time();
	$query = $db->prepare("INSERT INTO messages (subject, body, accID, userID, userName, toAccountID, timestamp) VALUES (:subject, :body, :accID, :userID, :userName, :toAccountID, :uploadDate)");
	$query->execute([':subject' => $subject, ':body' => $body, ':accID' => $accountID, ':userID' => $userID, ':userName' => $userName, ':toAccountID' => $toAccountID, ':uploadDate' => $uploadDate]);
	echo 1;
}
?>