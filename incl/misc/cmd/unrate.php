<?php
function unrate($gs, $commentarray, $uploadDate, $accountID, $levelID) {
    include dirname(__FILE__)."/../../lib/connection.php";
    $keepDiff = $commentarray[1];
    if (isset($keepDiff) AND is_numeric($keepDiff)) {
        if ($keepDiff >= 1) {
            $keepDiff = 1;
        } elseif ($keepDiff <= 0) {
            $keepDiff = 0;
        }
    } else {
        $keepDiff = 0;
    }
    $starCoins = $commentarray[2];
    $starFeature = $commentarray[3];
    if ($keepDiff == 1) {
        $query = $db->prepare("UPDATE levels SET starStars='0', starDemon='0', starAuto='0' WHERE levelID=:levelID");
        $query->execute([':levelID' => $levelID]);
        $query = $db->prepare("SELECT starStars, starDemon, starAuto FROM levels WHERE levelID=:levelID");
        $query->execute([':levelID' => $levelID]);
        $levelDiff = $query->fetchColumn();
        $query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES ('1', :value, '0', :levelID, :timestamp, :id)");
        $query->execute([':value' => $gs->getDifficulty($levelDiff["starStars"], $levelDiff["starAuto"], $levelDiff["starDemon"]),':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
    } else {
        $query = $db->prepare("UPDATE levels SET starStars='0', starDifficulty='0', starDemon='0', starAuto='0' WHERE levelID=:levelID");
        $query->execute([':levelID' => $levelID]);
        $query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES ('1', 'na', '0', :levelID, :timestamp, :id)");
        $query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
    }
    if(isset($starFeatured) AND is_numeric($starFeatured)){
        if ($starFeatured >= 1) {
            $starFeatured = 0;
        } elseif ($starFeatured <= 0) {
            $starFeatured = 1;
        }
        if ($starFeatured == 0) {
            if ($gs->checkPermission($accountID, "commandFeature")) {
                $query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES ('2', :value, :levelID, :timestamp, :id)");
                $query->execute([':value' => $starFeatured, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);	
                $query = $db->prepare("UPDATE levels SET starFeatured='0' WHERE levelID=:levelID");
                $query->execute([':levelID' => $levelID]);
            }
        }
    }
    if (isset($starCoins) AND is_numeric($starCoins)) {
        if ($starCoins >= 1) {
            $starCoins = 0;
        } elseif ($starCoins <= 0) {
            $starCoins = 1;
        }
        if ($starCoins == 0) {
            if ($gs->checkPermission($accountID, "commandVerifycoins")) {
                $query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES ('3', :value, :levelID, :timestamp, :id)");
                $query->execute([':value' => $starCoins, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
                $query = $db->prepare("UPDATE levels SET starCoins='0' WHERE levelID=:levelID");
                $query->execute([':levelID' => $levelID]);
            }
        }
    }
    return true;
}
?>
