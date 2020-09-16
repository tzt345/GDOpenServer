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
    $unverifyCoins = $commentarray[2];
    $unfeature = $commentarray[3];
    if ($keepDiff == 1) {
        $query = $db->prepare("UPDATE levels SET starStars='0', starDemon='0', starAuto='0' WHERE levelID=:levelID");
        $query->execute([':levelID' => $levelID]);
        $query = $db->prepare("SELECT starDifficulty FROM levels WHERE levelID=:levelID");
        $query->execute([':levelID' => $levelID]);
        $levelDiff = $query->fetchColumn();
        $query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES ('1', :value, '0', :levelID, :timestamp, :id)");
        $query->execute([':value' => $gs->getDifficulty($levelDiff, 0, 0),':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
    } else {
        $query = $db->prepare("UPDATE levels SET starStars='0', starDifficulty='0', starDemon='0', starAuto='0' WHERE levelID=:levelID");
        $query->execute([':levelID' => $levelID]);
        $query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES ('1', 'na', '0', :levelID, :timestamp, :id)");
        $query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
    }
    if(isset($unfeature) AND is_numeric($unfeature)){
        if ($unfeature >= 1) {
            $unfeature = 1;
        } elseif ($unfeature <= 0) {
            $unfeature = 0;
        }
    } else {
        $unfeature = 1;
    }
    if ($unfeature == 1) {
        if ($gs->checkPermission($accountID, "commandFeature")) {
            $query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES ('2', '0', :levelID, :timestamp, :id)");
            $query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);	
            $query = $db->prepare("UPDATE levels SET starFeatured='0' WHERE levelID=:levelID");
            $query->execute([':levelID' => $levelID]);
        }
    }
    if (isset($unverifyCoins) AND is_numeric($unverifyCoins)) {
        if ($unverifyCoins >= 1) {
            $unverifyCoins = 1;
        } elseif ($unverifyCoins <= 0) {
            $unverifyCoins = 0;
        }
    } else {
        $unverifyCoins = 1;
    }
    if ($unverifyCoins == 1) {
        if ($gs->checkPermission($accountID, "commandVerifycoins")) {
            $query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES ('3', '0', :levelID, :timestamp, :id)");
            $query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
            $query = $db->prepare("UPDATE levels SET starCoins='0' WHERE levelID=:levelID");
            $query->execute([':levelID' => $levelID]);
        }
    }
    return true;
}
?>
