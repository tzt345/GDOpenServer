<?php
include "../../incl/lib/connection.php";
require "../../incl/lib/exploitPatch.php";
$exploit_patch = new exploitPatch();
include "../../config/security.php";
// here begins the checks
if(!empty($_POST["username"]) AND !empty($_POST["email"]) AND !empty($_POST["repeatemail"]) AND !empty($_POST["password"]) AND !empty($_POST["repeatpassword"])){
	// catching all the input
	$username = $exploit_patch->remove($_POST["username"]);
	$password = $exploit_patch->remove($_POST["password"]);
	$repeat_password = $exploit_patch->remove($_POST["repeatpassword"]);
	$email = $exploit_patch->remove($_POST["email"]);
	$repeat_email = $exploit_patch->remove($_POST["repeatemail"]);
	if(strlen($username) < 3){
		// choose a longer username
		echo 'Username should be more than 3 characters.<br><br><form action="registerAccount.php" method="post">Username: <input type="text" name="username" maxlength=15><br>Password: <input type="password" name="password" maxlength=20><br>Repeat Password: <input type="password" name="repeatpassword" maxlength=20><br>Email: <input type="email" name="email" maxlength=50><br>Repeat Email: <input type="email" name="repeatemail" maxlength=50><br><input type="submit" value="Register"></form>';
	}elseif(strlen($password) < 6){
		// just why did you want to give a short password? do you wanna be hacked?
		echo 'Password should be more than 6 characters.<br><br><form action="registerAccount.php" method="post">Username: <input type="text" name="username" maxlength=15><br>Password: <input type="password" name="password" maxlength=20><br>Repeat Password: <input type="password" name="repeatpassword" maxlength=20><br>Email: <input type="email" name="email" maxlength=50><br>Repeat Email: <input type="email" name="repeatemail" maxlength=50><br><input type="submit" value="Register"></form>';
	}else{
		// this checks if there is another account with the same username as your input
		$query = $db->prepare("SELECT count(*) FROM accounts WHERE userName LIKE :userName");
		$query->execute([':userName' => $username]);
		$registred_users = $query->fetchColumn();
		if($registred_users > 0){
			// why did you want to make a new account with the same username as someone else's
			echo 'Username already taken.<br><br><form action="registerAccount.php" method="post">Username: <input type="text" name="username" maxlength=15><br>Password: <input type="password" name="password" maxlength=20><br>Repeat Password: <input type="password" name="repeatpassword" maxlength=20><br>Email: <input type="email" name="email" maxlength=50><br>Repeat Email: <input type="email" name="repeatemail" maxlength=50><br><input type="submit" value="Register"></form>';
		}else{
			if($password != $repeat_password){
				// this is when the passwords do not match
				echo 'Passwords do not match.<br><br><form action="registerAccount.php" method="post">Username: <input type="text" name="username" maxlength=15><br>Password: <input type="password" name="password" maxlength=20><br>Repeat Password: <input type="password" name="repeatpassword" maxlength=20><br>Email: <input type="email" name="email" maxlength=50><br>Repeat Email: <input type="email" name="repeatemail" maxlength=50><br><input type="submit" value="Register"></form>';
			}elseif($email != $repeat_email){
				// this is when the emails dont match
				echo 'Emails do not match.<br><br><form action="registerAccount.php" method="post">Username: <input type="text" name="username" maxlength=15><br>Password: <input type="password" name="password" maxlength=20><br>Repeat Password: <input type="password" name="repeatpassword" maxlength=20><br>Email: <input type="email" name="email" maxlength=50><br>Repeat Email: <input type="email" name="repeatemail" maxlength=50><br><input type="submit" value="Register"></form>';
			}else{
				// hashing your password and registering your account
				$hashpass = password_hash($password, PASSWORD_DEFAULT);
				if ($accountVerification == 2) {
					$query = $db->prepare("INSERT INTO accounts (userName, password, email, saveData, registerDate, saveKey, isVerified) VALUES (:userName, :password, :email, '', :time, '', 0)");
					$query->execute([':userName' => $userName, ':password' => $hashpass, ':email' => $email, ':time' => time()]);
					echo "Account registred. Check your email for a link that verifies your account. <a href='..'>Go back to tools</a>";
				} elseif ($accountVerification == 1) {
					$query = $db->prepare("INSERT INTO accounts (userName, password, email, saveData, registerDate, saveKey, isVerified) VALUES (:userName, :password, :email, '', :time, '', 0)");
					$query->execute([':userName' => $userName, ':password' => $hashpass, ':email' => $email, ':time' => time()]);
					echo "Account registred. Go to <a href='verifyAccount.php'>this tool</a> and login to verify your account. <a href='..'>Go back to tools</a>";
				} else {
					$query = $db->prepare("INSERT INTO accounts (userName, password, email, saveData, registerDate, saveKey) VALUES (:userName, :password, :email, '', :time, '')");
					$query->execute([':userName' => $userName, ':password' => $hashpass, ':email' => $email, ':time' => time()]);
					echo "Account registred. No e-mail verification required, you can login. <a href='..'>Go back to tools</a>";
				}
				// there you go, you are registered.
			}
		}
	}
}else{
	// this is given when we dont have an input
	echo '<form action="registerAccount.php" method="post">Username: <input type="text" name="username" maxlength=15><br>Password: <input type="password" name="password" maxlength=20><br>Repeat Password: <input type="password" name="repeatpassword" maxlength=20><br>Email: <input type="email" name="email" maxlength=50><br>Repeat Email: <input type="email" name="repeatemail" maxlength=50><br><input type="submit" value="Register"></form>';
}
?>