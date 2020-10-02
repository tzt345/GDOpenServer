<?php
$query = $db->prepare("SELECT discordID, userName FROM accounts WHERE accountID = :accountID");
$query->execute([':accountID' => $accountID]);
$account = $query->fetch();
$gs->sendDiscordPM($account["discordID"], "Your Discord account has been unlinked from " . $account["userName"] . "!");
$query = $db->prepare("UPDATE accounts SET discordID = 0 WHERE accountID = :accountID");
$query->execute([':accountID' => $accountID]);
?>