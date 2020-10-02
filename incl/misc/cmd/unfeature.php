<?php
$query = $db->prepare("SELECT starFeatured, isCPShared FROM levels WHERE levelID = :levelID");
$query->execute([':levelID' => $levelID]);
$result = $query->fetch();
if ($result["starFeatured"] == 0) {
    $query = $db->prepare("UPDATE levels SET starFeatured = 0 WHERE levelID = :levelID");
    $query->execute([':levelID' => $levelID]);
    if ($result["isCPShared"] == 1) {
        $query3 = $db->prepare("SELECT userID FROM cpshares WHERE levelID = :levelID");
        $query3->execute([':levelID' => $levelID]);
        if ($CPSharedWhole == 1) {
            $addCP = $featureCP;
        } else {
            $sharecount = $query3->rowCount() + 1;
            $addCP = round($featureCP / $sharecount);
        }
        $shares = $query->fetchAll();
        $CPShare = round($addCP);
        foreach($shares as &$share){
            $query4 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints - :CPShare WHERE userID = :userID");
            $query4->execute([':userID' => $share["userID"], ':CPShare' => $CPShare]);
        }
    } else {
        $query4 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints - :addCP WHERE extID = :extID");
        $query4->execute([':extID' => $targetExtID, ':addCP' => $featureCP]);
    }
} else {
    exit("temp_0_Error: This level is already not Epic.");
}
$query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES (2, 0, :levelID, :timestamp, :id)");
$query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
exit("temp_0_The level has been unfeatured.");
?>