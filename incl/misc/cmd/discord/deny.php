<?php
$query = $db->prepare("SELECT discordLinkReq, userName FROM accounts WHERE accountID = :accountID");
$query->execute([':accountID' => $accountID]);
$account = $query->fetch();
$gs->sendDiscordPM($account["discordLinkReq"], "Your link request to " . $account["userName"] . " has been denied!");
$query = $db->prepare("UPDATE accounts SET discordLinkReq = 0 WHERE accountID = :accountID");
$query->execute([':accountID' => $accountID]);
?>