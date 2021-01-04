<?php
include "../incl/lib/connection.php";
require "../incl/lib/generatePass.php";
$gp = new generatePass();
require "../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
require "../incl/lib/mainLib.php";
$gs = new mainLib();
if(isset($_POST["userName"]) AND isset($_POST["password"]) AND isset($_POST["type"]) AND isset($_POST["amount"]) AND isset($_POST["reward"]) AND isset($_POST["questName"])){
	$userName = $ep->remove($_POST["userName"]);
	$password = $ep->remove($_POST["password"]);
	$type = $ep->number($_POST["type"]);
	$amount = $ep->number($_POST["amount"]);
	$reward = $ep->number($_POST["reward"]);
	$name = $ep->remove($_POST["questName"]);
	$pass = $gp->isValidUsrname($userName, $password);
	if ($pass == 1) {
		$query = $db->prepare("SELECT accountID FROM accounts WHERE userName=:userName");	
		$query->execute([':userName' => $userName]);
		$accountID = $query->fetchColumn();
		if($gs->checkPermission($accountID, "toolAddquest") == false){
			echo "This account doesn't have permission to access this tool. <a href='addQuest.php'>Try again.</a>";
		}else{
			if(!is_numeric($type) OR !is_numeric($amount) OR !is_numeric($reward) OR $type > 3){
				exit("Invalid Type/Amount/Reward. <a href='addQuest.php'>Try again.</a>");
			}
			$query = $db->prepare("INSERT INTO quests (type, amount, reward, name) VALUES (:type, :amount, :reward, :name)");
			$query->execute([':type' => $type, ':amount' => $amount, ':reward' => $reward, ':name' => $name]);
			$query = $db->prepare("INSERT INTO modactions (type, value, timestamp, account, value2, value3, value4) VALUES (17, :value, :timestamp, :account, :amount, :reward, :name)");
			$query->execute([':value' => $type, ':timestamp' => time(), ':account' => $accountID, ':amount' => $amount, ':reward' => $reward, ':name' => $name]);
			if($db->lastInsertId() < 3) {
				exit("Successfully added the quest! It's recommended to <a href='addQuest.php'>add</a> a few more.");
			} else {
				exit("Successfully added the quest! <a href='addQuest.php'>Add another?</a>");
			}
		}
	}else{
		echo "Invalid password or non-existent account. <a href='addQuest.php'>Try again.</a>";
	}
}else{
	echo '<form action="addQuest.php" method="post">Username: <input type="text" name="userName">
		<br>Password: <input type="password" name="password">
		<br>Quest Type: <select name="type">
			<option value="1">Orbs</option>
			<option value="2">Coins</option>
			<option value="3">Star</option>
		</select>
		<br>Amount: <input type="number" name="amount"> (How many orbs/coins/stars you need to collect)
		<br>Reward: <input type="number" name="reward"> (How many Diamonds you get as a reward)
		<br>Quest Name: <input type="text" name="questName">
		<input type="submit" value="Create"></form>';
}
?>
