<?php
require "../../incl/lib/connection.php";
require_once "../../incl/lib/generatePass.php";
$gp = new generatePass();
require_once "../../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
if (!empty($_POST["userName"]) AND !empty($_POST["password"])) {
	$userName = $ep->remove($_POST["userName"]);
	$password = $ep->remove($_POST["password"]);
	$pass = $gp->isValidUsrname($userName, $password);
	if ($pass == 1) {
		$query = $db->prepare("SELECT accountID FROM accounts WHERE userName = :userName");	
		$query->execute([':userName' => $userName]);
		if ($query->rowCount() == 0) {
			echo "Invalid password or non-existent account. <a href='unlisted.php'>Try again.</a>";
		} else {
			$accountID = $query->fetchColumn();
			$query = $db->prepare("SELECT levelID, levelName FROM levels WHERE extID = :extID AND unlisted = 1");	
			$query->execute([':extID' => $accountID]);
			$result = $query->fetchAll();
			echo '<table border="1"><tr><th>ID</th><th>Name</th></tr>';
			foreach ($result as &$level) {
				echo "<tr><td>" . $level["levelID"] . "</td><td>" . $level["levelName"] . "</td></tr>";
			}
			echo "</table>";
		}
	} else {
		echo "Invalid password or non-existent account. <a href='unlisted.php'>Try again</a>";
	}
} else {
	echo '<form action="unlisted.php" method="post">
		Username: <input type="text" name="userName" minlength=3 maxlength=15><br>
		Password: <input type="password" name="password" minlength=6 maxlength=20><br>
		<input type="submit" value="Show Unlisted Levels">
	</form>';
}
?>