<?php
$response = "";
if (isset($commentArray[1])) {
    $diffArray = $gs->getDiffFromName($commentArray[1]);
} else {
    exit("temp_0_Error: No input given.");
}
$starDifficulty = $diffArray[0];
$starDemon = $diffArray[1];
$starAuto = $diffArray[2];
if (isset($commentArray[2]) AND is_numeric($commentArray[2])) {
    $starStars = $commentArray[2];
} else {
    $starStars = 0;
}
if (isset($commentArray[3]) AND is_numeric($commentArray[3])) {
    $starFeatured = $commentArray[3];
    if ($starFeatured >= 1) {
        $starFeatured = 1;
    } else {
        $starFeatured = 0;
    }
} else {
    $starFeatured = 0;
}
if (isset($commentArray[4]) AND is_numeric($commentArray[4])) {
    $starCoins = $commentArray[4];
    if ($starCoins >= 1) {
        $starCoins = 1;
    } else {
        $starCoins = 0;
    }
} else {
    $starCoins = 0;
}

$featureLevel = 0;
if ($starStars != 0) {
    $response .= ucfirst($commentArray[1]) . " with $starStars stars";
    $query = $db->prepare("SELECT starStars, starFeatured, isCPShared FROM levels WHERE levelID = :levelID");
    $query->execute([':levelID' => $levelID]);
    $result = $query->fetch();
    if ($result["starStars"] == 0) {
        if ($result["isCPShared"] == 1) {
            $query3 = $db->prepare("SELECT userID FROM cpshares WHERE levelID = :levelID");
            $query3->execute([':levelID' => $levelID]);
            $deservedcp = $rateCP;
            if ($gs->checkPermission($accountID, "Feature") AND $result["starFeatured"] != 0) {
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
            foreach ($shares as &$share) {
                $query4 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints + :CPShare WHERE userID = :userID");
                $query4->execute([':userID' => $share["userID"], ':CPShare' => $CPShare]);
            }
        } else {
            $query4 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints + :addCP WHERE extID = :extID");
            $query4->execute([':extID' => $targetExtID, ':addCP' => $rateCP]);
        }
    }
} else {
    $response .= ucfirst($commentArray[1]) . " with no stars";
}
$query = $db->prepare("UPDATE levels SET starStars = :starStars, starDifficulty = :starDifficulty, starDemon = :starDemon, starAuto = :starAuto WHERE levelID = :levelID");
$query->execute([':starStars' => $starStars, ':starDifficulty' => $starDifficulty, ':starDemon' => $starDemon, ':starAuto' => $starAuto, ':levelID' => $levelID]);
$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (1, :value, :value2, :levelID, :timestamp, :id)");
$query->execute([':value' => ucfirst($commentArray[1]), ':timestamp' => $uploadDate, ':id' => $accountID, ':value2' => $starStars, ':levelID' => $levelID]);
if ($starFeatured == 1 AND $featureLevel == 1) {
    $query = $db->prepare("UPDATE levels SET starFeatured = 1 WHERE levelID = :levelID");
    $query->execute([':levelID' => $levelID]);
    $query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES (2, 1, :levelID, :timestamp, :id)");
    $query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
    $response .= " and featured it";
}
if ($starCoins == 1 AND $gs->checkPermission($accountID, "Verifycoins")) {
    $query = $db->prepare("UPDATE levels SET starCoins = 1 WHERE levelID = :levelID");
    $query->execute([':levelID' => $levelID]);
    $query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES (3, 1, :levelID, :timestamp, :id)");
    $query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
    $response .= "and verified it's coins";
}
exit("temp_0_Level successfully rated to $response.");
?>
