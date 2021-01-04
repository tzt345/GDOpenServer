<title>Change chest rewards</title>
<?php
// THIS IS A SUPER LAZY TOOL AND MIGHT NEED TO BE RE-WORKED ON, BUT FOR NOW IT **JUST WORKS**
if(!empty($_POST["pwcheck"])) {
	require "../config/connection.php";
	if ($_POST["pwcheck"] != $password) {
		exit("Wrong password!<br><br>");
	}
	// small chest
	$smolMinOrbs = $_POST["smolMiO"] || 200;
	$smolMaxOrbs = $_POST["smolMaO"] || 400;
	$smolMinDiamonds = $_POST["smolMiD"] || 2;
	$smolMaxDiamonds = $_POST["smolMaD"] || 10;
	$smolMinShards = $_POST["smolMiS"] || 1;
	$smolMaxShards = $_POST["smolMaS"] || 6;
	$smolMinKeys = $_POST["smolMiK"] || 1;
	$smolMaxKeys = $_POST["smolMaK"] || 6;
	// big chest
	$bigMinOrbs = $_POST["bigMiO"] || 200;
	$bigMaxOrbs = $_POST["bigMaO"] || 400;
	$bigMinDiamonds = $_POST["bigMiD"] || 2;
	$bigMaxDiamonds = $_POST["bigMaD"] || 10;
	$bigMinShards = $_POST["bigMiS"] || 1;
	$bigMaxShards = $_POST["bigMaS"] || 6;
	$bigMinKeys = $_POST["bigMiK"] || 1;
	$bigMaxKeys = $_POST["bigMaK"] || 6;
	// timings
	$smolWait = $_POST["smolWait"] || 3600;
	$bigWait = $_POST["bigWait"] || 14400;
	// ono
	$string = "<?php".PHP_EOL;
	$string .= '$chest1minOrbs = '.$smolMinOrbs.';'.PHP_EOL;
	$string .= '$chest1maxOrbs = '.$smolMaxOrbs.';'.PHP_EOL;
	$string .= '$chest1minDiamonds = '.$smolMinDiamonds.';'.PHP_EOL;
	$string .= '$chest1maxDiamonds = '.$smolMaxDiamonds.';'.PHP_EOL;
	$string .= '$chest1minShards = '.$smolMinShards.';'.PHP_EOL;
	$string .= '$chest1maxShards = '.$smolMaxShards.';'.PHP_EOL;
	$string .= '$chest1minKeys = '.$smolMinKeys.';'.PHP_EOL;
	$string .= '$chest1maxKeys = '.$smolMaxKeys.';'.PHP_EOL;
	$string .= '$chest2minOrbs = '.$bigMinOrbs.';'.PHP_EOL;
	$string .= '$chest2maxOrbs = '.$bigMaxOrbs.';'.PHP_EOL;
	$string .= '$chest2minDiamonds = '.$bigMinDiamonds.';'.PHP_EOL;
	$string .= '$chest2maxDiamonds = '.$bigMaxDiamonds.';'.PHP_EOL;
	$string .= '$chest2minShards = '.$bigMinShards.';'.PHP_EOL;
	$string .= '$chest2maxShards = '.$bigMaxShards.';'.PHP_EOL;
	$string .= '$chest2minKeys = '.$bigMinKeys.';'.PHP_EOL;
	$string .= '$chest2maxKeys = '.$bigMaxKeys.';'.PHP_EOL;
	$string .= '$chest1wait = '.$smolWait.';'.PHP_EOL;
	$string .= '$chest2wait = '.$bigWait.';'.PHP_EOL;
	$string .= "?>".PHP_EOL;
	try {
		file_put_contents("../config/dailyChests.php", $string);
		exit ("Success!");
	} catch (Exception $e) {
		exit ("Couldn't change rewards...");
	}
	
}
require "../config/dailyChests.php";
?>
<form action="" method="POST">
	<!-- Don't even try to SQL-Inject, there's no SQL query involved in here! ;) -->
	<span style="opacity:25%">Tool made by Rya#8632 - please <b>tag</b> him before making a ticket if you have issues.</span>
	Database Password <input name="pwcheck" type="password" placeholder="Check GDPS Bot DM"/><br>
	<b>Small Chest rewards</b><br>
	Min. Orbs <input name="smolMiO" type="number" value="<?php echo ($chest1minOrbs); ?>" min=0 /><br>
	Max. Orbs <input name="smolMaO" type="number" value="<?php echo ($chest1maxOrbs); ?>" min=0 /><br>
	Min. Diamonds <input name="smolMiD" type="number" value="<?php echo ($chest1minDiamonds); ?>" min=0 /><br>
	Max. Diamonds <input name="smolMaD" type="number" value="<?php echo ($chest1maxDiamonds); ?>" min=0 /><br>
	Min. Shards <input name="smolMiS" type="number" value="<?php echo ($chest1minShards); ?>" min=0 max=6 /><br>
	Max. Shards (up to 6) <input name="smolMaS" type="number" value="<?php echo ($chest1maxShards); ?>" min=0 max=6 /><br>
	Min. Keys <input name="smolMiK" type="number" value="<?php echo ($chest1minKeys); ?>" min=0 max=6 /><br>
	Max. Keys (up to 6) <input name="smolMaK" type="number" value="<?php echo ($chest1maxKeys); ?>" min=0 max=6 /><br>
	<b>Big Chest rewards</b><br>
	Min. Orbs <input name="bigMiO" type="number" value="<?php echo ($chest2minOrbs); ?>" min=0 /><br>
	Max. Orbs <input name="bigMaO" type="number" value="<?php echo ($chest2maxOrbs); ?>" min=0 /><br>
	Min. Diamonds <input name="bigMiD" type="number" value="<?php echo ($chest2minDiamonds); ?>" min=0 /><br>
	Max. Diamonds <input name="bigMaD" type="number" value="<?php echo ($chest2maxDiamonds); ?>" min=0 /><br>
	Min. Shards<input name="bigMiS" type="number" value="<?php echo ($chest2minShards); ?>" min=0 max=6 /><br>
	Max. Shards<input name="bigMaS" type="number" value="<?php echo ($chest2maxShards); ?>" min=0 max=6 /><br>
	Min. Keys <input name="bigMiK" type="number" value="<?php echo ($chest2minKeys); ?>" min=0 max=6 /><br>
	Max. Keys <input name="bigMaK" type="number" value="<?php echo ($chest2maxKeys); ?>" min=0 max=6 /><br>
	<b>Waiting duration (in seconds!)</b><br>
	Small Chest <input name="smolWait" type="number" value="<?php echo ($chest1wait); ?>" min=0 /><br>
	Big Chest <input name="bigWait" type="number" value="<?php echo ($chest2wait); ?>" min=0 /><br>
	<input type="submit" value="Apply" />
</form>