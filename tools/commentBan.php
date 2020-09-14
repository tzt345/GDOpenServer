<?php
include "../incl/lib/connection.php";
require "../incl/lib/generatePass.php";
$gp = new generatePass();
require "../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
require "../incl/lib/mainLib.php";
$gs = new mainLib();
if(!empty($_POST["userName"]) AND !empty($_POST["password"]) AND !empty($_POST["userID"]) AND !empty($_POST["banType"]) AND !empty($_POST["banReason"])){
	$userName = $ep->remove($_POST["userName"]);
	$password = $ep->remove($_POST["password"]);
	$userID = $ep->remove($_POST["userID"]);
	$banType = $ep->remove($_POST["banType"]);
	$banReason = $ep->remove($_POST["banReason"]);
	$pass = $gp->isValidUsrname($userName, $password);
	if ($pass == 1) {
		$query = $db->prepare("SELECT accountID FROM accounts WHERE userName=:userName");	
		$query->execute([':userName' => $userName]);
		$accountID = $query->fetchColumn();
		if($gs->checkPermission($accountID, "toolCommentban") == false){
			exit ("This account doesn't have the permissions to access this tool. <a href='commentBan.php'>Try again</a>");
		}else{
			$query = $db->prepare("UPDATE users SET isCommentBanned = :ban, commentBanReason = :banReason WHERE userID = :id");
			$query->execute([':id' => $userID, ':ban' => $banType, ':banReason' => $banReason]);
			$query = $db->prepare("INSERT INTO modactions (type, value, timestamp, account, value2, value4) VALUES ('15', :value, :timestamp, :userID, :banType, :banReason)");
            $query->execute([':value' => $userName, ':timestamp' => time(), ':userID' => $userID, ':banType' => $banType, ':banReason' => $banReason]);
            if($banType == 3){
                echo "Unban successful";
            } else {
            	echo "Comment ban successful<br>";
            }
		}
	} else {
		exit ("Wrong password! <a href='commentBan.php'>Try again</a>.");
	}
} else {
	echo '<form action="commentBan.php" method="post">Your Username: <input type="text" name="userName">
	<br>Your Password: <input type="password" name="password">
	<br>Target UserID: <input type="text" name="userID">
	<br>Ban Type: <select name="banType">
		<option value="1">Temporary Ban (not supported yet)</option>
		<option value="2">Permanent Ban</option>
		<option value="3">Unban</option>
	</select>
	<br>Ban Reason: <input type="text" name="banReason">
	<br><input type="submit" value="Ban"></form>';
}
?>	