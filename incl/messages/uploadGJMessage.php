<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
if (empty($_POST["gjp"]) OR empty($_POST["accountID"]) OR empty($_POST["toAccountID"]) OR empty($_POST["subject"])) OR empty($_POST["body"]))) {
	exit("-1");
}
$gjp = $ep->remove($_POST["gjp"]);
$accountID = $ep->remove($_POST["accountID"]);
$gjpresult = $GJPCheck->check($gjp, $accountID);
if ($gjpresult != 1) {
	exit("-1");
}
$toAccountID = $ep->number($_POST["toAccountID"]);
if ($accountID == $toAccountID) {
	exit("-1");
}
$subject = $ep->remove($_POST["subject"]);
$body = $ep->remove($_POST["body"]);

$query2 = $db->prepare("SELECT count(*) FROM blocks WHERE person1 = :toAccountID AND person2 = :accountID LIMIT 1");
$query2->execute([':toAccountID' => $toAccountID, ':accountID' => $accountID]);
$blocked = $query2->fetchColumn();
$query3 = $db->prepare("SELECT mS FROM accounts WHERE accountID = :toAccountID AND mS != 0 LIMIT 1");
$query3->execute([':toAccountID' => $toAccountID]);
$mSOnly = $query3->fetchColumn();
$query4 = $db->prepare("SELECT count(*) FROM friendships WHERE (person1 = :accountID AND person2 = :toAccountID) AND (person2 = :accountID AND person1 = :toAccountID) LIMIT 1");
$query4->execute([':toAccountID' => $toAccountID, ':accountID' => $accountID]);
$friend = $query4->fetchColumn();

if ($blocked == 1 OR $mSOnly == 2) {
	echo "-1";
} elseif (($friend == 1 AND $mSOnly == 1) OR $mSOnly == 0) {
	$query = $db->prepare("SELECT userName FROM users WHERE extID = :accountID LIMIT 1");
	$query->execute([':accountID' => $accountID]);
	$userName = $query->fetchColumn();
	$userID = $gs->getUserID($accountID);
	$query = $db->prepare("INSERT INTO messages (subject, body, accID, userID, userName, toAccountID, timestamp) VALUES (:subject, :body, :accID, :userID, :userName, :toAccountID, :uploadDate)");
	$query->execute([':subject' => $subject, ':body' => $body, ':accID' => $accountID, ':userID' => $userID, ':userName' => $userName, ':toAccountID' => $toAccountID, ':uploadDate' => time()]);
	echo "1";
} else {
	echo "-1";
}
?>