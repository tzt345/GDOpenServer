<?php
$query = $db->prepare("SELECT starStars, starFeatured, starEpic, starMagic, isCPShared FROM levels WHERE levelID = :levelID");
$query->execute([':levelID' => $levelID]);
$result = $query->fetch();
$deservedCP = 0;
$deservedCP2 = 0;
if ($result["starFeatured"] == 0) {
    $deservedCP2 = $featureCP;
} else {
    exit("temp_0_Error: This level is already featured.");
}
$deservedCP += $epicCP;
if ($result["starStars"] != 0) {
    $deservedCP += $starCP;
}
if ($result["starEpic"] != 0) {
    $deservedCP += $epicCP;
}
if ($isMagicSectionManual == 1 AND $result["starMagic"] != 0) {
    $deservedCP += $magicCP;
}
$deservedCP2 += $deservedCP;
$removeCP = round($deservedCP);
$addCP = round($deservedCP2);
if ($removeCP > 0) {
    if ($result["isCPShared"] == 1) {
        $query2 = $db->prepare("SELECT userID FROM cpshares WHERE levelID = :levelID");
        $query2->execute([':levelID' => $levelID]);
        $shares = $query2->fetch(); 
        foreach ($shares as &$share){
            $query3 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints - :CPShare WHERE userID = :userID");
            $query3->execute([':userID' => $share["userID"], ':CPShare' => $removeCP]);
            $query4 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints + :CPShare WHERE userID = :userID");
            $query4->execute([':userID' => $share["userID"], ':CPShare' => $addCP]);
        }
    } else {
        $query2 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints - :CPShare WHERE userID = :userID");
        $query2->execute([':userID' => $targetExtID, ':CPShare' => $removeCP]);
        $query3 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints + :CPShare WHERE userID = :userID");
        $query3->execute([':userID' => $targetExtID, ':CPShare' => $addCP]);
    }
}
$query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES (2, 1, :levelID, :timestamp, :id)");
$query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
exit("temp_0_The level has been featured.");
?>