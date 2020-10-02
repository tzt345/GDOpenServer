<?php
if (isset($commentarray[1]) AND is_numeric($commentarray[1])) {
    $keepDiff = $commentarray[1];
    if ($keepDiff >= 1) {
        $keepDiff = 1;
    } else {
        $keepDiff = 0;
    }
} else {
    $keepDiff = 0;
}
if (isset($commentarray[2]) AND is_numeric($commentarray[2])) {
    $unfeature = $commentarray[2];
    if ($unfeature >= 1) {
        $unfeature = 1;
    } else {
        $unfeature = 0;
    }
} else {
    $unfeature = 0;
}
if (isset($commentarray[3]) AND is_numeric($commentarray[3])) {
    $unverifyCoins = $commentarray[3];
    if ($unverifyCoins >= 1) {
        $unverifyCoins = 1;
    } else {
        $unverifyCoins = 0;
    }
} else {
    $unverifyCoins = 0;
}

$response = "unrated";
$featureLevel = 0;
$query = $db->prepare("SELECT starStars, starFeatured, isCPShared FROM levels WHERE levelID=:levelID");
$query->execute([':levelID' => $levelID]);
$result = $query->fetch();
if ($result["starStars"] == 0) {
    if ($result["isCPShared"] == 1) {
        $query3 = $db->prepare("SELECT userID FROM cpshares WHERE levelID = :levelID");
        $query3->execute([':levelID' => $levelID]);
        $deservedcp = $rateCP;
        if($gs->checkPermission($accountID, "commandFeature") AND $result["starFeatured"] == 0){
            $deservedcp += $featureCP;
            $featureLevel = 1;
        }
        if ($CPSharedWhole == 1) {
            $addCP = $deservedcp;
        } else {
            $sharecount = $query3->rowCount() + 1;
            $addCP = round($deservedcp / $sharecount);
        }
        $shares = $query->fetchAll();
        $CPShare = round($addCP);
        foreach($shares as &$share){
            $query4 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints + :CPShare WHERE userID = :userID");
            $query4->execute([':userID' => $share["userID"], ':CPShare' => $CPShare]);
        }
    } else {
        $query4 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints + :addCP WHERE extID = :extID");
        $query4->execute([':extID' => $targetExtID, ':addCP' => $rateCP]);
    }
}
if ($keepDiff == 1) {
    $query = $db->prepare("UPDATE levels SET starStars = 0, starDemon = 0, starAuto = 0 WHERE levelID = :levelID");
    $query->execute([':levelID' => $levelID]);
    $query = $db->prepare("SELECT starDifficulty FROM levels WHERE levelID=:levelID");
    $query->execute([':levelID' => $levelID]);
    $levelDiff = $query->fetchColumn();
    $query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (1, :value, 0, :levelID, :timestamp, :id)");
    $query->execute([':value' => $gs->getDifficulty($levelDiff, 0, 0), ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
    $response .= " without changing the difficulty";
} else {
    $query = $db->prepare("UPDATE levels SET starStars = 0, starDifficulty = 0, starDemon = 0, starAuto = 0 WHERE levelID = :levelID");
    $query->execute([':levelID' => $levelID]);
    $query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (1, 'N/A', 0, :levelID, :timestamp, :id)");
    $query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
}
if ($unfeature == 1) {
    if ($featureLevel == 1) {
        $query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES (2, 0, :levelID, :timestamp, :id)");
        $query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
        $query = $db->prepare("UPDATE levels SET starFeatured=0 WHERE levelID=:levelID");
        $query->execute([':levelID' => $levelID]);
        $response .= " and unfeatured";
    }
}
if ($unverifyCoins == 1) {
    if ($gs->checkPermission($accountID, "commandVerifycoins")) {
        $query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES (3, 0, :levelID, :timestamp, :id)");
        $query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
        $query = $db->prepare("UPDATE levels SET starCoins = 0 WHERE levelID = :levelID");
        $query->execute([':levelID' => $levelID]);
        $response .= " and coins unverified"; 
    }
}
exit("temp_0_Level successfully $response.");
?>
