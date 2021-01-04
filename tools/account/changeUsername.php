<?php
require "../../incl/lib/connection.php";
require_once "../../incl/lib/generatePass.php";
$gp = new generatePass();
require_once "../../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
//here im getting all the data
if (isset($_POST["userName"]) AND isset($_POST["newUser"]) AND isset($_POST["password"])) {
	$userName = $ep->remove($_POST["userName"]);
	$newUser = $ep->remove($_POST["newUser"]);
	$password = $ep->remove($_POST["password"]);
	$pass = $gp->isValidUsrname($userName, $password);
	if ($pass == 1) {
		$query = $db->prepare("SELECT accountID FROM accounts WHERE userName = :userName");	
		$query->execute([':userName' => $userName]);
		if ($query->rowCount() == 0) {
			echo "Invalid password or non-existent account. <a href='changeUsername.php'>Try again.</a>";
		} else {
			$accountID = $query->fetchColumn();
			$query = $db->prepare("UPDATE accounts SET userName = :newUser WHERE accountID = :accountID");	
			$query->execute([':newUser' => $newUser, ':accountID' => $accountID]);
			$query = $db->prepare("UPDATE users SET userName = :newUser WHERE extID = :extID");	
			$query->execute([':newUser' => $newUser, ':extID' => $accountID]);
			echo "Username changed. <a href='index.php'>Go back to account management.</a>";
		}
	} else {
		echo "Invalid password or non-existent account. <a href='changeUsername.php'>Try again.</a>";
	}
} else {
	echo '<form action="changeUsername.php" method="post">
		Old Username: <input type="text" name="userName"><br>
		New Username: <input type="text" name="newUser"><br>
		Password: <input type="password" name="password"><br>
		<input type="submit" value="Change">
	</form>';
}
?>