<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
if (empty($_POST["gjp"]) OR empty($_POST["requestID"]) OR empty($_POST["accountID"])) {
	exit("-1");
}
$gjp = $ep->remove($_POST["gjp"]);
$accountID = $ep->remove($_POST["accountID"]);
$gjpresult = $GJPCheck->check($gjp, $accountID);
if ($gjpresult != 1) {
	exit("-1");
}
$requestID = $ep->remove($_POST["requestID"]);
// ACCEPTING FOR USER 2
$query = $db->prepare("SELECT accountID, toAccountID FROM friendreqs WHERE ID = :requestID");
$query->execute([':requestID' => $requestID]);
$request = $query->fetch();
$reqAccountID = $request["accountID"];
$toAccountID = $request["toAccountID"];
if ($toAccountID != $accountID) {
	exit("-1");
}
$query = $db->prepare("INSERT INTO friendships (person1, person2, isNew1, isNew2) VALUES (:accountID, :targetAccountID, 1, 1)");
$query->execute([':accountID' => $reqAccountID, ':targetAccountID' => $toAccountID]);
//REMOVING THE REQUEST
$query = $db->prepare("DELETE from friendreqs WHERE ID = :requestID LIMIT 1");
$query->execute([':requestID' => $requestID]);
//Success response
echo "1";
?>