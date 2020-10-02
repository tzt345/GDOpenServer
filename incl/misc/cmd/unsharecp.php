<?php
if (isset($commentarray[1])) {
    $userName = $commentarray[1];
} else {
    exit("temp_0_Error: No input given for required argument 'User'.");
}
$query = $db->prepare("SELECT userID, isCreatorBanned FROM users WHERE extID = :userName OR userName = :userName ORDER BY isRegistered DESC LIMIT 1");
$query->execute([':userName' => $userName]);
$result = $query->fetch();
if ($query->rowCount() == 0) {
    exit("temp_0_Error: No user found with the name or account ID '$userName'.");
} elseif ($targetExtID == $accountID) {
    exit("temp_0_Error: You cannot unshare the awarded creator points of this level with yourself.");
}
$userID = $result["userID"];
$query = $db->prepare("SELECT starStars, starFeatured, starEpic, starMagic, isCPShared FROM levels WHERE levelID = :levelID");
$query->execute([':levelID' => $levelID]);
$result = $query->fetch();
$deservedcp = 0;
if($result["starStars"] != 0){
    $deservedcp += $rateCP;
}
if($result["starFeatured"] != 0){
    $deservedcp += $featureCP;
}
if($result["starEpic"] != 0){
    $deservedcp += $epicCP;
}
if($isMagicSectionManual == 1 AND $result["starMagic"] != 0){
    $deservedcp += $magicCP;
}
$query3 = $db->prepare("SELECT userID FROM cpshares WHERE levelID = :levelID");
$query3->execute([':levelID' => $levelID]);
$shares = $query->fetchAll();
if ($CPSharedWhole == 1) {
    $addCP = $deservedcp;
} else {
    $sharecount = $query3->rowCount() + 1;
    $sharecount2 = $query3->rowCount();
    $addCP = round($deservedcp / $sharecount);
    $addCP2 = round($deservedcp / $sharecount2);
}
if ($result["isCPShared"] == 1) {
    $CPShare = round($addCP);
    foreach($shares as &$share){
        $query4 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints - :CPShare WHERE userID = :userID");
        $query4->execute([':userID' => $share["userID"], ':CPShare' => $CPShare]);
    }
    $query = $db->prepare("DELETE FROM cpshares WHERE levelID = :levelID AND userID = :userID)");
    $query->execute([':userID' => $userID, ':levelID' => $levelID]);
    $query3 = $db->prepare("SELECT userID FROM cpshares WHERE levelID = :levelID");
    $query3->execute([':levelID' => $levelID]);
    $shares2 = $query->fetchAll();
    $CPShare2 = round($addCP2);
    foreach($shares2 as &$share){
        $query4 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints + :CPShare WHERE userID = :userID");
        $query4->execute([':userID' => $share["userID"], ':CPShare' => $CPShare2]);
    }
} else {
    $query4 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints - :addCP WHERE extID = :extID");
    $query4->execute([':extID' => $targetExtID, ':addCP' => $addCP]);
    $query = $db->prepare("DELETE FROM cpshares WHERE levelID = :levelID AND userID = :userID)");
    $query->execute([':userID' => $userID, ':levelID' => $levelID]);
    $query3 = $db->prepare("SELECT userID FROM cpshares WHERE levelID = :levelID");
    $query3->execute([':levelID' => $levelID]);
    $shares = $query->fetchAll();
    $CPShare = round($addCP);
    foreach($shares as &$share){
        $query4 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints - :CPShare WHERE userID = :userID");
        $query4->execute([':userID' => $share["userID"], ':CPShare' => $CPShare]);
    }
}
$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (11, 1, :value, :levelID, :timestamp, :id)");
$query->execute([':value' => $userName, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
exit("temp_0_Creator Points awarded on this level are no longer shared with '$userName'.");
?>