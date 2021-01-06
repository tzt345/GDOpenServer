<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
//here im getting all the data
if (empty($_POST["accountID"]) OR empty($_POST["toAccountID"]) OR empty($_POST["gjp"])) {
	exit("-1");
}
$accountID = $ep->number($_POST["accountID"]);
$gjp = $ep->remove($_POST["gjp"]);
$gjpresult = $GJPCheck->check($gjp, $accountID);
if ($gjpresult != 1) {
	exit("-1");
}
$toAccountID = $ep->number($_POST["toAccountID"]);
$comment = $ep->remove($_POST["comment"]);
$blocked = $db->query("SELECT ID FROM blocks WHERE person1 = $toAccountID AND person2 = $accountID")->fetchAll(PDO::FETCH_COLUMN);
$frSOnly = $db->query("SELECT frS FROM accounts WHERE accountID = $toAccountID AND frS = 1")->fetchAll(PDO::FETCH_COLUMN);
$query = $db->prepare("SELECT count(*) FROM friendreqs WHERE (accountID = :accountID AND toAccountID = :toAccountID) OR (toAccountID = :accountID AND accountID = :toAccountID)");
$query->execute([':accountID' => $accountID, ':toAccountID' => $toAccountID]);
if ($query->fetchColumn() == 0) {
	if (empty($blocked[0]) and empty($frSOnly[0])) {
		$query = $db->prepare("INSERT INTO friendreqs (accountID, toAccountID, comment, uploadDate) VALUES (:accountID, :toAccountID, :comment, :uploadDate)");
		$query->execute([':accountID' => $accountID, ':toAccountID' => $toAccountID, ':comment' => $comment, ':uploadDate' => time()]);
		echo "1";
	} else {
		echo "-1";
	}
} else {
	echo "-1";
}
?>