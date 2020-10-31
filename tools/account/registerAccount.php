<?php
session_start();
require "../../config/security.php";
// here begins the checks
if(!empty($_POST["username"]) AND !empty($_POST["email"]) AND !empty($_POST["password"]) AND !empty($_POST["repeatpassword"])){
	require "../../incl/lib/connection.php";
	require "../../incl/lib/exploitPatch.php";
	$exploit_patch = new exploitPatch();
	// catching all the input
	$username = $exploit_patch->remove($_POST["username"]);
	$password = $_POST["password"];
	$repeat_password = $_POST["repeatpassword"];
	$email = $exploit_patch->remove($_POST["email"]);
	if(strlen($username) < 3){
		echo 'Username should be more than 3 characters.';
	}elseif(strlen($password) < 6){
		echo 'Password should be more than 6 characters.';
	}else{
		// this checks if there is another account with the same username or email as your input
		$query = $db->prepare("SELECT count(*) FROM accounts WHERE email LIKE :email");
		$query->execute([':email' => $email]);
		$registred_emails = $query->fetchColumn();
		$query = $db->prepare("SELECT count(*) FROM accounts WHERE userName LIKE :userName");
		$query->execute([':userName' => $username]);
		$registred_users = $query->fetchColumn();
		if($registred_emails > 0 AND $accountVerification == 2){
			// I am too lazy to put a check if there are more than 1 account with the same mail, shouldn't break anything, hopefully
			echo 'E-Mail already taken. You should contact the server owner if you belive this is an error.';
		}elseif($registred_users == 1){
			echo 'Username already taken.';
		}else{
			if($password != $repeat_password){
				echo 'Passwords do not match.';
			}else{
				// hashing your password and registering your account
				$hashpass = password_hash($password, PASSWORD_DEFAULT);
				if ($accountVerification == 2) {
					if(isset($_POST["captcha"]) AND $_POST["captcha"] != "" AND $_SESSION["code"] == $_POST["captcha"]) {
						require "../../incl/lib/mainLib.php";
						require "../../incl/email/sendMail.php";
						$ss = new mainLib();
						$secret = $ss->randomString(16);
						$query = $db->prepare("INSERT INTO accounts (userName, password, email, saveData, registerDate, saveKey, isVerified, verifySecret) VALUES (:userName, :password, :email, '', :time, '', 0, :secret)");
						$query->execute([':userName' => $username, ':password' => $hashpass, ':email' => $email, ':time' => time(), ':secret' => $secret]);
						$accountID = $db->lastInsertId();
						sendVerificationMail($email, $secret, $accountID);
						echo "Account registred. Check your E-Mail (spam-) inbox to verify your account. <a href='..'>Go back to the tools page.</a>";
					} else {
						echo "Captcha verification failed. Please try again.";
					}
				} elseif ($accountVerification == 1) {
					if(isset($_POST["captcha"]) AND $_POST["captcha"] != "" AND $_SESSION["code"] == $_POST["captcha"]) {
						$query = $db->prepare("INSERT INTO accounts (userName, password, email, saveData, registerDate, saveKey, isVerified) VALUES (:userName, :password, :email, '', :time, '', 0)");
						$query->execute([':userName' => $username, ':password' => $hashpass, ':email' => $email, ':time' => time()]);
						echo "Account registred. No E-Mail verification required, you can login. <a href='..'>Go back to the Tools page.</a>";
					} else {
						echo "Captcha verification failed. Please try again.";
					}
				} else {
					$query = $db->prepare("INSERT INTO accounts (userName, password, email, saveData, registerDate, saveKey) VALUES (:userName, :password, :email, '', :time, '')");
					$query->execute([':userName' => $username, ':password' => $hashpass, ':email' => $email, ':time' => time()]);
					echo "Account registred. No E-Mail verification required, you can login. <a href='..'>Go back to the Tools page.</a>";
				}
			}
		}
	}
	echo "<br><br>";
}
?>
<form action="registerAccount.php" method="post">
Username: <input type="text" name="username" maxlength=15><br>
Password: <input type="password" name="password" maxlength=20><br>
Repeat Password: <input type="password" name="repeatpassword" maxlength=20><br>
E-Mail: <input type="email" name="email" maxlength=50><br>
<?php if ($accountVerification == 2) { ?> (Make sure to enter your real E-Mail!)
<?php } if ($accountVerification >= 1) { /* practically useless, but since I haven't worked on an auto-expiry system for unverified accounts, this will stay to prevent bots */ ?>
Verify Captcha: <input name="captcha" type="text"><br>
<img src="../../incl/misc/captchaGen.php" /><br><br>
<?php } ?>
<input type="submit" value="Register"></form>