<?php
session_start();
require "../../config/security.php";
// here begins the checks
if (isset($_POST["userName"]) AND isset($_POST["email"]) AND isset($_POST["password"]) AND isset($_POST["repeatPassword"])){
	require "../../incl/lib/connection.php";
	require_once "../../incl/lib/exploitPatch.php";
	$ep = new exploitPatch();
	// catching all the input
	$userName = $ep->remove($_POST["userName"]);
	$password = $_POST["password"];
	$repeatPassword = $_POST["repeatPassword"];
	$email = $ep->remove($_POST["email"]);
	// this checks if there is another account with the same username or email as your input
	$query = $db->prepare("SELECT count(*) FROM accounts WHERE email LIKE :email");
	$query->execute([':email' => $email]);
	$registeredEmails = $query->fetchColumn();
	$query = $db->prepare("SELECT count(*) FROM accounts WHERE userName LIKE :userName");
	$query->execute([':userName' => $userName]);
	$registredUsers = $query->fetchColumn();
	if ($accountVerification == 2 AND $registredEmails > 0) {
		// I am too lazy to put a check if there are more than 1 account with the same mail, shouldn't break anything, hopefully
		echo 'E-Mail already taken. You should contact the server owner if you belive this is an error.';
	} elseif ($registredUsers > 0) {
		echo 'Username already taken.';
	} else {
		if ($password != $repeatPassword) {
			echo 'Passwords do not match.';
		} else {
			// hashing your password and registering your account
			$hashpass = password_hash($password, PASSWORD_DEFAULT);
			switch ($accountVerification) {
				case 2:
					if (isset($_POST["captcha"]) AND $_POST["captcha"] != "" AND $_SESSION["code"] == $_POST["captcha"]) {
						require "../../incl/lib/mainLib.php";
						$gs = new mainLib();
						require "../../incl/email/sendMail.php";
						$secret = $gs->randomString(16);
						$query = $db->prepare("INSERT INTO accounts (userName, password, email, saveData, registerDate, saveKey, isVerified, verifySecret) VALUES (:userName, :password, :email, '', :time, '', 0, :secret)");
						$query->execute([':userName' => $userName, ':password' => $hashpass, ':email' => $email, ':time' => time(), ':secret' => $secret]);
						$accountID = $db->lastInsertId();
						sendVerificationMail($email, $secret, $accountID);
						echo "Account registred. Check your E-Mail inbox to verify your account (Remember to check your spam emails too). Go back to <a href='..'>the tools page.</a>";
					} else {
						echo "Captcha verification failed. Please try again.";
					}
				case 1:
					if (isset($_POST["captcha"]) AND $_POST["captcha"] != "" AND $_SESSION["code"] == $_POST["captcha"]) {
						$query = $db->prepare("INSERT INTO accounts (userName, password, email, saveData, registerDate, saveKey, isVerified) VALUES (:userName, :password, :email, '', :time, '', 0)");
						$query->execute([':userName' => $userName, ':password' => $hashpass, ':email' => $email, ':time' => time()]);
						echo "Account registred. No E-Mail verification required, you can login. Go back to <a href='index.php'>account management.</a>";
					} else {
						echo "Captcha verification failed. Please try again.";
					}
				default:
					$query = $db->prepare("INSERT INTO accounts (userName, password, email, saveData, registerDate, saveKey) VALUES (:userName, :password, :email, '', :time, '')");
					$query->execute([':userName' => $userName, ':password' => $hashpass, ':email' => $email, ':time' => time()]);
					echo "Account registred. No E-Mail verification required, you can login. Go back to <a href='index.php'>account management.</a>";
			}
		}
	}
	echo "<br><br>";
}
?>
<form action="registerAccount.php" method="post">
	Username: <input type="text" name="userName" minlength=3 maxlength=15><br>
	Password: <input type="password" name="password" minlength=6 maxlength=20><br>
	Repeat Password: <input type="password" name="repeatPassword" minlength=6 maxlength=20><br>
	Email
<?php if ($accountVerification == 2) { ?>  (Make sure to enter your real E-Mail!)
<?php } ?>
: <input type="email" name="email" maxlength=50>
<?php if ($accountVerification >= 1) { /* practically useless, but since I haven't worked on an auto-expiry system for unverified accounts, this will stay to prevent bots */ ?> 
<br>Verify Captcha: <input name="captcha" type="text"><br>
<img src="../../incl/misc/captchaGen.php" /><br><br>
<?php } ?>
	<input type="submit" value="Register">
</form>