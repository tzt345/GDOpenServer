<?php
session_start();
if(isset($_GET["secret"]) AND isset($_GET["ID"])) {
	if(is_numeric($_GET["secret"]) AND is_numeric($_GET["ID"])) {
		include "../../incl/lib/connection.php";
		$query = $db->prepare("SELECT verifySecret FROM accounts WHERE accountID = :acc");
		$query->execute([':acc' => $_GET["ID"]]);
		$secret = $query->fetchColumn();
		if ($secret == $_GET["secret"]) {
			$query = $db->prepare("UPDATE accounts SET verifySecret = '', isVerified = 1 WHERE accountID = :acc");
			$query->execute([':acc' => $_GET["ID"]]);
			header("refresh:5;url=../index.php");
			exit("Successfully verified. You'll be redirected in a bit...");
		} else {
			exit("Secret does not match. If you belive this is an error, contact the server owner.");
		}
	} else {
		exit("Invalid input.");
	}
} elseif (!empty($_POST["username"]) AND !empty($_POST["password"]) AND !empty(["captcha"])) {
	include "../../incl/lib/generatePass.php";
	include "../../incl/lib/exploitPatch.php";
	include "../../incl/lib/connection.php";
	$ep = new exploitPatch();
	$gp = new generatePass();
	$usr = $ep->remove($_POST["username"]);
	$psw = $_POST["password"];
	if($gp->isValidUsrname($usr,$psw) AND $_POST["captcha"] != "" AND $_SESSION["code"] == $_POST["captcha"]) {
		$query = $db->prepare("UPDATE accounts SET isVerified = 1 WHERE username = :usr");
		$query->execute([':usr' => $usr]);
		echo "Account verified successfully, you can now login. <a href='..'>Go back to the Tools page.</a>";
	} else {
		echo "Username/Password incorrect. <a href='verifyAccount.php'>Try again.</a>";
	}
} else {
?>
<form action="verifyAccount.php" method="post">
Username: <input type="text" name="username" maxlength=15><br>
Password: <input type="password" name="password" maxlength=20><br>
Verify Captcha: <input name="captcha" type="text"><br>
<img src="captchagen.php" /><br><br>
<input type="submit" value="Verify"></form>
<?php } ?>