<?php
require "../../incl/lib/connection.php";
require_once "../../incl/lib/generatePass.php";
$gp = new generatePass();
require_once "../../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../../incl/lib/mainLib.php";
$gs = new mainLib();
//here im getting all the data
if (isset($_POST["userName"]) AND isset($_POST["newUser"]) AND isset($_POST["password"])) {
	$userName = $ep->remove($_POST["userName"]);
	$newUserName = $ep->remove($_POST["newUserName"]);
	$password = $ep->remove($_POST["password"]);
	$pass = $gp->isValidUsrname($userName, $password);
	if ($pass == 1) {
		$accountID = $query->fetchColumn();
		$query = $db->prepare("UPDATE accounts SET userName = :newUserName WHERE accountID = :accountID");	
		$query->execute([':newUser' => $newUserName, ':accountID' => $accountID]);
		$query = $db->prepare("UPDATE users SET userName = :newUserName WHERE extID = :extID");	
		$query->execute([':newUser' => $newUserName, ':extID' => $accountID]);
		echo "Username changed. Go back to <a href='index.php'>account management.</a>";
	} else {
		echo "Invalid password or non-existent account. <a href='changeUsername.php'>Try again.</a>";
	}
} else {
	echo '<form action="changeUsername.php" method="post">
		Old Username: <input type="text" name="userName"><br>
		New Username: <input type="text" name="newUserName" minlength=3 maxlength=15><br>
		Password: <input type="password" name="password" minlength=6 maxlength=20><br>
		<input type="submit" value="Change">
	</form>';
}
?>