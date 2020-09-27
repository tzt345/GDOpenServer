<?php
session_start();
include "../../incl/lib/connection.php";
require "../../incl/lib/exploitPatch.php";
$exploit_patch = new exploitPatch();
include "../../config/security.php";
// here begins the checks
if(!empty($_POST["username"]) AND !empty($_POST["email"]) AND !empty($_POST["password"]) AND !empty($_POST["repeatpassword"])){
	// catching all the input
	$username = $exploit_patch->remove($_POST["username"]);
	$password = ($_POST["password"]);
	$repeat_password = ($_POST["repeatpassword"]);
	$email = FILTER_SANITIZE_EMAIL($_POST["email"]);
	if(strlen($username) < 3){
		echo 'Username should be more than 3 characters.';
	}elseif(strlen($password) < 6){
		echo 'Password should be more than 6 characters.';
	}else{
		// this checks if there is another account with the same username as your input
		$query = $db->prepare("SELECT count(*) FROM accounts WHERE userName LIKE :userName");
		$query->execute([':userName' => $username]);
		$registred_users = $query->fetchColumn();
		if($registred_users > 0){
			echo 'Username already taken.';
		}else{
			if($password != $repeat_password){
				// this is when the passwords do not match
				echo 'Passwords do not match.';
			}else{
				// hashing your password and registering your account
				$hashpass = password_hash($password, PASSWORD_DEFAULT);
				if ($accountVerification == 2) {
					/*
					include "../../incl/lib/mainLib.php";
					include __DIR__."/email.php";
					$ss = new mainLib();
					$secret = $ss->randomString(16);
					$query = $db->prepare("INSERT INTO accounts (userName, password, email, saveData, registerDate, saveKey, isVerified, verifySecret) VALUES (:userName, :password, :email, '', :time, '', 0, :secret)");
					$query->execute([':userName' => $userName, ':password' => $hashpass, ':email' => $email, ':time' => time(), ':secret' => $secret]);
					$accountID = $db->lastInsertId();
					sendVerificationMail($email,$secret,$accountID);
					echo "Account registred. Check your E-Mail inbox to verify your account. <a href='..'>Go back to the tools page.</a>";
					*/
					echo "E-Mail registration is still in work, please check in later.";
				} elseif ($accountVerification == 1) {
					if(isset($_POST["captcha"])&&$_POST["captcha"]!=""&&$_SESSION["code"]==$_POST["captcha"]) {
						$query = $db->prepare("INSERT INTO accounts (userName, password, email, saveData, registerDate, saveKey, isVerified) VALUES (:userName, :password, :email, '', :time, '', 0)");
						$query->execute([':userName' => $userName, ':password' => $hashpass, ':email' => $email, ':time' => time()]);
						echo "Account registred. No E-Mail verification required, you can login. <a href='..'>Go back to the Tools page.</a>";
					} else {
						echo "Captcha verification failed. Please try again.";
					}
				} else {
					$query = $db->prepare("INSERT INTO accounts (userName, password, email, saveData, registerDate, saveKey) VALUES (:userName, :password, :email, '', :time, '')");
					$query->execute([':userName' => $userName, ':password' => $hashpass, ':email' => $email, ':time' => time()]);
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
Email: <input type="email" name="email" maxlength=50><br>
<?php if ($accountVerification >= 1) { ?>
Verify Captcha: <input name="captcha" type="text"><br>
<img src="captchagen.php" /><br><br>
<?php } ?>
<input type="submit" value="Register"></form>