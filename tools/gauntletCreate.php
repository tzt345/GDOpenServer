<?php
include "../incl/lib/connection.php";
require "../incl/lib/generatePass.php";
$generatePass = new generatePass();
require "../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
require "../incl/lib/mainLib.php";
$gs = new mainLib();
if(!empty($_POST["userName"]) AND !empty($_POST["password"]) AND !empty($_POST["gauntletID"])){
	$userName = $ep->remove($_POST["userName"]);
	$password = $_POST["password"];
	$pass = $generatePass->isValidUsrname($userName, $password);
	if ($pass != 1) {
		echo "Invalid password or non-existant account. <a href='gauntletCreate.php'>Try again.</a>";
	}
	if (!is_numeric($_POST["level1"]) OR !is_numeric($_POST["level2"]) OR !is_numeric($_POST["level3"]) OR !is_numeric($_POST["level4"]) OR !is_numeric($_POST["level5"])){
		exit("Invalid level IDs. <a href='gauntletCreate.php'>Try again.</a>");
	}
	$ID = $ep->remove($_POST["gauntletID"]);
	$level1 = $ep->number($_POST["level1"]);
	$level2 = $ep->number($_POST["level2"]);
	$level3 = $ep->number($_POST["level3"]);
	$level4 = $ep->number($_POST["level4"]);
	$level5 = $ep->number($_POST["level5"]);
	$query = $db->prepare("SELECT accountID FROM accounts WHERE userName=:userName");	
	$query->execute([':userName' => $userName]);
	$accountID = $query->fetchColumn();
	if ($gs->checkPermission($accountID, "toolPackcreate") == false) {
		echo "This account doesn't have the permissions to access this tool. <a href='gauntletCreate.php'>Try again</a>";
	} else {
		$query = $db->prepare("DELETE FROM `gauntlets` WHERE `ID` = ?");
		$query->execute(array($ID));
		exit('Successfully deleted gauntlet.');
		$query = $db->prepare("INSERT INTO gauntlets (ID, level1, level2, level3, level4, level5) VALUES (?, ?, ?, ?, ?, ?)");
		$query->execute(array($ID, $level1, $level2, $level3, $level4, $level5));
		echo "Success!";
	}
}else{
	echo '<form action="gauntletCreate.php" method="post">Username: <input type="text" name="userName">
		<br>Password: <input type="password" name="password">
		<br>Gauntlet ID: <input type="text" name="gauntletID"> A list of Gauntlet ID\'s can be found in the GDOpenServer Docs.
		<br><b>Insert only Level ID\'s, one for each box in order.</b> If you wish to delete a Gauntlet, leave the Boxes below empty.
		<br>Level 1: <input type="text" name="level1" size="2"> 
		Level 2: <input type="text" name="level2" size="2"> 
		Level 3: <input type="text" name="level3" size="2"> 
		Level 4: <input type="text" name="level4" size="2"> 
		Level 5: <input type="text" name="level5" size="2">
		<br><br><input type="submit" value="Create / Update Gauntlet."></form>';
}
?>