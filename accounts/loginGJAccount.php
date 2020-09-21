<?php
include "../incl/lib/connection.php";
require "../incl/lib/generatePass.php";
$generatePass = new generatePass();
require_once "../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../incl/lib/mainLib.php";
$gs = new mainLib();
include "../config/users.php";
//here im getting all the data
$ip = $gs->getIP();
$udid = $ep->remove($_POST["udid"]);
$userName = $ep->remove($_POST["userName"]);
$password = $ep->remove($_POST["password"]);
//registering
$query = $db->prepare("SELECT accountID FROM accounts WHERE userName LIKE :userName");
$query->execute([':userName' => $userName]);
if($query->rowCount() == 0){
	exit("-1");
}
$id = $query->fetchColumn();
//rate limiting
if ($loginRateLimitCountToDisable != 0 AND $loginRateLimitDisableTime != 0) {
	$query6 = $db->prepare("SELECT count(*) FROM actions WHERE type = 1 AND timestamp > :time AND value2 = :ip");
	$query6->execute([':time' => time() - ($loginRateLimitDisableTime * 60), ':ip' => $ip]);
	if($query6->fetchColumn() >= $loginRateLimitCountToDisable){
		exit("-12");
	}
}
//authenticating
$pass = $generatePass->isValidUsrname($userName, $password);
if ($pass == 1) { //success
	//userID
	$query2 = $db->prepare("SELECT userID, isBanned, isDisabled FROM users WHERE extID = :id");
	$query2->execute([':id' => $id]);
	if ($query2->rowCount() > 0) {
		$result = $query2->fetch();
		$userID = $result["userID"];
		if ($result["isBanned"] == 1 OR $result["isDisabled"] == 1) {
			exit("-12");
		}
	} else {
		$query = $db->prepare("INSERT INTO users (isRegistered, extID, userName) VALUES (1, :id, :userName)");
		$query->execute([':id' => $id, ':userName' => $userName]);
		$userID = $db->lastInsertId();
	}
	//logging
	$query6 = $db->prepare("INSERT INTO actions (type, value, timestamp, value2) VALUES (2, :username, :time, :ip)");
	$query6->execute([':username' => $userName, ':time' => time(), ':ip' => $ip]);
	//result
	echo $id.",".$userID;
	if(!is_numeric($udid)){
		$query2 = $db->prepare("SELECT userID FROM users WHERE extID = :udid");
		$query2->execute([':udid' => $udid]);
		$usrid2 = $query2->fetchColumn();
		$query2 = $db->prepare("UPDATE levels SET userID = :userID, extID = :extID WHERE userID = :usrid2");
		$query2->execute([':userID' => $userID, ':extID' => $id, ':usrid2' => $usrid2]);	
	}
}elseif ($pass == -1){ //failure
	echo -12;
}else{
	echo -1;
}
?>