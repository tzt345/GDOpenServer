<?php
include "../../config/security.php";
session_start();
if($accountVerification == 2) {
	if(isset($_GET["secret"]) AND isset($_GET["ID"]) AND is_numeric($_GET["ID"])) {
		include "../../incl/lib/connection.php";
		require "../../incl/lib/exploitPatch.php";
		$ep = new exploitPatch();
		$accountID = $ep->remove($_GET["ID"]);
		$secret = $ep->remove($_GET["secret"]);
		$query = $db->prepare("SELECT verifySecret, isVerified FROM accounts WHERE accountID = :acc");
		$query->execute([':acc' => $accountID]);
		$result = $query->fetch();
		if ($result["isVerified"] == 1) {
			exit("Your account is already verified! You can proceed to login in-game.");
		} elseif (!isset($result["verifySecret"]) OR $result["verifySecret"] != $secret) {
			exit("Secrets do not match. If you belive this is an error, contact the server owner.");
		} else {
			$query = $db->prepare("UPDATE accounts SET verifySecret = '', isVerified = 1 WHERE accountID = :acc");
			$query->execute([':acc' => $accountID]);
			exit("Account verified successfully, you can now login. <a href='index.php'>Go to account management.</a>");
		}
	} else {
		exit("Invalid input.");
	}
} elseif ($accountVerification <= 1) {
	include "../../incl/lib/connection.php";
	include "../../incl/lib/generatePass.php";
	$gp = new generatePass();
	include "../../incl/lib/exploitPatch.php";
	$ep = new exploitPatch();
	if (isset($_POST["userName"]) AND isset($_POST["password"]) AND isset(["captcha"]))
		$userName = $ep->remove($_POST["userName"]);
		$password = $_POST["password"];
		if($gp->isValidUsrname($userName, $password) AND $_POST["captcha"] != "" AND $_SESSION["code"] == $_POST["captcha"]) {
			$query = $db->prepare("SELECT isVerified FROM accounts WHERE accountID = :acc");
			$query->execute([':acc' => $accountID]);
			if ($result["isVerified"] == 1) {
				exit("Your account is already verified! You can proceed to login in-game.");
			}
			$query = $db->prepare("UPDATE accounts SET isVerified = 1 WHERE userName = :userName");
			$query->execute([':userName' => $userName]);
			echo "Account verified successfully, you can now login. <a href='index.php'>Go back to account management.</a>";
		} else {
			echo "Password or Captcha incorrect. <a href='verifyAccount.php'>Try again.</a>";
		}
	}
} else {
	echo "If during the time verification was enforced and you couldn't verify your account at that moment, fill the form below.<br><br>";
}
if ($accountVerification <= 1) { ?>
<form action="verifyAccount.php" method="post">
Username: <input type="text" name="userName" maxlength=15><br>
Password: <input type="password" name="password" maxlength=20><br>
Verify Captcha: <input name="captcha" type="text"><br>
<img src="../../incl/misc/captchaGen.php" /><br><br>
<input type="submit" value="Verify"></form>
<?php } ?>