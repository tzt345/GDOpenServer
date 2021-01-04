<?php
require "../incl/lib/connection.php";
require "../config/discord.php";
require_once "../incl/lib/mainLib.php";
$gs = new mainLib();
if ($discordEnabled != 1) {
	exit("Discord integration is disabled.");
}
$discordID = $_GET["discordID"];
$newpass = $gs->randomString();
$passhash = password_hash($newpass, PASSWORD_DEFAULT);
$query = $db->prepare("UPDATE accounts SET password = :password WHERE discordID = :discordID");	
$query->execute([':password' => $passhash, ':discordID' => $discordID]);
$gs->sendDiscordPM($discordID, "Password changed to $newpass\r\nFor your security we advise you to go change your password to http://pi.michaelbrabec.cz:9010/a/tools/account/changePassword.php");
echo "Please check your DMs";
?>