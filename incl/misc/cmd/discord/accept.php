<?php
$query = $db->prepare("UPDATE accounts SET discordID = discordLinkReq, discordLinkReq = 0 WHERE accountID = :accountID AND discordLinkReq <> 0");
$query->execute([':accountID' => $accountID]);
$query = $db->prepare("SELECT discordID, userName FROM accounts WHERE accountID = :accountID");
$query->execute([':accountID' => $accountID]);
$account = $query->fetch();
$gs->sendDiscordPM($account["discordID"], "Your link request to " . $account["userName"] . " has been accepted!");
?>