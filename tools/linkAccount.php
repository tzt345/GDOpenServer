<html>
<head>
<title>Account Linking</title>
</head>
<body>
<?php
require "../incl/lib/connection.php";
require_once "../incl/lib/generatePass.php";
$gp = new generatePass();
require_once "../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
if (!empty($_POST["userName"]) AND !empty($_POST["password"]) AND !empty($_POST["targetUserName"]) AND !empty($_POST["targetPassword"])) {
	$userName = $ep->remove($_POST["userName"]);
	$password = $ep->remove($_POST["password"]);
	$targetUserName = $ep->remove($_POST["targetUserName"]);
	$targetPassword = $ep->remove($_POST["targetPassword"]);
	$pass = $gp->isValidUsrname($userhere, $passhere);
	if ($pass == 1) {
		$url = $_POST["server"];
		$udid = "S" . mt_rand(111111111, 999999999) . mt_rand(111111111, 999999999) . mt_rand(111111111, 999999999) . mt_rand(111111111, 999999999) . mt_rand(1, 9);
		$sid = mt_rand(111111111, 999999999) . mt_rand(11111111, 99999999);
		$post = ['userName' => $targetUserName, 'udid' => $udid, 'password' => $targetPassword, 'sID' => $sid, 'secret' => 'Wmfv3899gc9'];
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$result = curl_exec($ch);
		curl_close($ch);
		if ($result == "" OR $result == "-1" OR $result == "No no no") {
			if ($result == "") {
				echo "An error has occured while connecting to the server. <a href='linkAccount.php>Try again.</a>";
			} elseif ($result == "-1") {
				echo "Login to the target server failed. <a href='linkAccount.php>Try again.</a>";
			} else {
				echo "RobTop doesn't like you or something.... <a href='linkAccount.php>Try again.</a>";
			}
			echo "<br>Error code: $result";
		} else {
			if ($_POST["debug"] == 1) {
				echo "<br>$result<br>";
			}
			$parsedurl = parse_url($url);
			if ($parsedurl["host"] == $_SERVER['SERVER_NAME']) {
				exit("You can't link 2 accounts on the same server. <a href='linkAccount.php>Try again.</a>");
			}
			//getting stuff
			$query = $db->prepare("SELECT accountID FROM accounts WHERE userName = :userName LIMIT 1");
			$query->execute([':userName' => $userhere]);
			$accountID = $query->fetchColumn();
			$query = $db->prepare("SELECT userID FROM users WHERE extID = :extID LIMIT 1");
			$query->execute([':extID' => $accountID]);
			$userID = $query->fetchColumn();
			$targetAccountID = explode(",", $result)[0];
			$targetUserID = explode(",", $result)[1];
			$query = $db->prepare("SELECT count(*) FROM links WHERE targetAccountID = :targetAccountID AND server = " . $parsedurl["host"] . " LIMIT 1");
			$query->execute([':targetAccountID' => $targetAccountID]);
			if ($query->fetchColumn() != 0) {
				exit("The target account is linked to an account already. <a href='linkAccount.php>Try again.</a>");
			}
			if (!is_numeric($targetAccountID) OR !is_numeric($accountID)) {
				exit("Invalid Account ID (Account corrupted, please contact the GDPS owner). <a href='linkAccount.php>Try again.</a>");
			}
			$server = $parsedurl["host"];
			//query
			$query = $db->prepare("INSERT INTO links (accountID, targetAccountID, server, timestamp, userID, targetUserID) VALUES (:accountID, :targetAccountID, :server, :timestamp, :userID, :targetUserID)");
			$query->execute([':accountID' => $accountID, ':targetAccountID' => $targetAccountID, ':server' => $server, ':timestamp' => time(), 'userID' => $userID, 'targetUserID' => $targetUserID]);
			echo "Account linked succesfully. <a href='index.php'>Go back to main tools page.</a>";
		}
	} else {
		echo "Invalid local username/password combination. <a href='linkAccount.php>Try again.</a>";
	}
} else {
	echo 'Your password for the target server is NOT saved, it\'s used for one-time verification purposes only.<br>
	<form action="linkAccount.php" method="post">
		<h3>This GDPS<h3>
		Username: <input type="text" name="userName" minlength=3 maxlength=15><br>
		Password: <input type="password" name="password" minlength=6 maxlength=20><br>
		<h3>Target server</h3>
		Username: <input type="text" name="targetUserName" minlength=3 maxlength=15><br>
		Password: <input type="password" name="targetPassword" minlength=6 maxlength=20><br>
		URL (Don\'t change if you don\'t know what you are doing): <input type="text" name="server" value="http://www.boomlings.com/database/accounts/loginGJAccount.php"><br>
		Debug Mode (0=off, 1=on): <input type="text" name="debug" value="0"><br>
		<input type="submit" value="Link Accounts">
	</form><br>
	Alternative servers to link to:<br>
	http://www.boomlings.com/database/accounts/loginGJAccount.php - Main Geometry Dash server<br>
	http://pi.michaelbrabec.cz:9010/a/accounts/loginGJAccount.php - CvoltonGDPS<br>
	http://teamhax.altervista.org/dbh/accounts/loginGJAccount.php - TeamHax GDPS';
}
?>
</body>
</html>