<?php
include "../incl/lib/connection.php";
require "../incl/lib/generatePass.php";
$generatePass = new generatePass();
require_once "../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../incl/lib/mainLib.php";
$gs = new mainLib();
if(!empty($_POST["userName"]) AND !empty($_POST["password"]) AND !empty($_POST["levelID"]) AND !empty($_POST["timestamp"])){
	$userName = $ep->remove($_POST["userName"]);
	$password = $ep->remove($_POST["password"]);
	$levelID = $ep->remove($_POST["levelID"]);
	$timestamp = $ep->remove($_POST["timestamp"]);
	$pass = $generatePass->isValidUsrname($userName, $password);

	if ($pass == 1) {
		$query = $db->prepare("SELECT accountID FROM accounts WHERE userName=:userName");	
		$query->execute([':userName' => $userName]);
		$accountID = $query->fetchColumn();
		if($gs->checkPermission($accountID, "toolRevertlikes")){
			if(!is_numeric($levelID)) {
				exit("Invalid level ID. <a href='revertLikes.php'>Try again.</a>");
			}
			$query = $db->prepare("SELECT count(*) FROM actions WHERE value = :levelID AND type = 3 AND timestamp >= :timestamp");
			$query->execute([':levelID' => $levelID, ':timestamp' => $timestamp]);
			$count = $query->fetchColumn();

			$query = $db->prepare("UPDATE levels SET likes = likes + :count WHERE levelID = :levelID");
			$query->execute([':levelID' => $levelID, ':count' => $count]);

			if($query->rowCount() != 0){
				echo "Reverted likes succesfully. <a href='revertLikes.php'>Go back.</a>";
			}else{
				echo "Reverting likes failed. <a href='revertLikes.php'>Try again.</a>";
			}

			$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (19, :levelID, 1, :now, :account)");
			$query->execute([':levelID' => $levelID, ':timestamp' => $timestamp, ':now' => time(), ':account' => $accountID]);

		}else{
			exit("You do not have the permission to do this action. <a href='revertLikes.php'>Try again.</a>");
		}
	}else{
		echo "Invalid password or non-existant account. <a href='revertLikes.php'>Try again.</a>";
	}
}else{
	echo '<form action="revertLikes.php" method="post">Your Username: <input type="text" name="userName">
		<br>Your Password: <input type="password" name="password">
		<br>Level ID: <input type="text" name="levelID">
		<br>Timestamp since: <input type="text" name="timestamp">
		<br><input type="submit" value="Revert"></form>';
}
?>