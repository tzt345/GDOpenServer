<?php
$query = $db->prepare("SELECT starStars, starFeatured, starEpic, starMagic, isCPShared FROM levels WHERE levelID = :levelID");
$query->execute([':levelID' => $levelID]);
$result = $query->fetch();
$deservedCP = 0;
$deservedCP2 = 0;
if ($result["starEpic"] == 0) {
    $deservedCP2 = $epicCP;
} else {
    exit("temp_0_Error: This level is already Epic.");
}
$deservedCP += $epicCP;
if ($result["starStars"] != 0) {
    $deservedCP += $rateCP;
}
if ($result["starFeatured"] != 0) {
    $deservedCP += $featureCP;
}
if ($isMagicSectionManual == 1 AND $result["starMagic"] != 0) {
    $deservedCP += $magicCP;
}
$deservedCP2 += $deservedCP;
$removeCP = round($deservedCP);
$addCP = round($deservedCP2);
if ($addCP - $removeCP != 0) {
    if ($result["isCPShared"] == 1) {
        $query2 = $db->prepare("SELECT userID FROM cpshares WHERE levelID = :levelID");
        $query2->execute([':levelID' => $levelID]);
        $shares = $query2->fetch(); 
        foreach ($shares as &$share) {
            $query4 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints + :CPShare WHERE userID = :userID");
            $query4->execute([':userID' => $share["userID"], ':CPShare' => $addCP - $removeCP]);
        }
    } else {
        $query3 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints + :CPShare WHERE userID = :userID");
        $query3->execute([':userID' => $targetExtID, ':CPShare' => $addCP - $removeCP]);
    }
}



/* $query = $db->prepare("SELECT starEpic, isCPShared FROM levels WHERE levelID = :levelID");
$query->execute([':levelID' => $levelID]);
$result = $query->fetch();
if ($result["starEpic"] == 0) {
    $query = $db->prepare("UPDATE levels SET starEpic = 1 WHERE levelID = :levelID");
    $query->execute([':levelID' => $levelID]);
    if ($result["isCPShared"] == 1) {
        $query3 = $db->prepare("SELECT userID FROM cpshares WHERE levelID = :levelID");
        $query3->execute([':levelID' => $levelID]);
        if ($CPSharedWhole == 1) {
            $addCP = $epicCP;
        } else {
            $sharecount = $query3->rowCount() + 1;
            $addCP = round($epicCP / $sharecount);
        }
        $shares = $query3->fetchAll();
        $CPShare = round($addCP);
        foreach($shares as &$share){
            $query4 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints + :CPShare WHERE userID = :userID");
            $query4->execute([':userID' => $share["userID"], ':CPShare' => $CPShare]);
        }
    } else {
        $query4 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints + :addCP WHERE extID = :extID");
        $query4->execute([':extID' => $targetExtID, ':addCP' => $epicCP]);
    }
} else {
    exit("temp_0_Error: This level is already Epic.");
} */
$query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES (4, 1, :levelID, :timestamp, :id)");
$query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
exit("temp_0_The level is now Epic.");
?>