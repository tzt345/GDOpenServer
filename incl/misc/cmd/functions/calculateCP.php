<?php
function calculateCP($checkType = 1, $levelID, $targetExtID) {
    $query = $db->prepare("SELECT starStars, starFeatured, starEpic, starMagic, isCPShared FROM levels WHERE levelID = :levelID");
    $query->execute([':levelID' => $levelID]);
    $result = $query->fetch();
    $deservedCP = 0;
    $deservedCP2 = 0;

    if ($result["starStars"] != 0) {
        if ($checkType == 1) {
            exit("temp_0_Error: This level is already Star rated.");
        } elseif ($checkType == -1) {
            $deservedCP2 += $starCP;
            $deservedCP += $starCP;
        } else {
            $deservedCP += $starCP;
        }
    } elseif ($checkType == -1) {
        exit("temp_0_Error: This level is already not Star rated.");
    } elseif ($checkType == 1) {
        $deservedCP2 += $starCP;
        $deservedCP += $starCP;
    } else {
        $deservedCP += $starCP;
    }
    if ($result["starFeature"] != 0) {
        if ($checkType == 2) {
            exit("temp_0_Error: This level is already Featured.");
        } elseif ($checkType == -2) {
            $deservedCP2 += $featureCP;
            $deservedCP += $featureCP;
        } else {
            $deservedCP += $featureCP;
        }
    } elseif ($checkType == -2) {
        exit("temp_0_Error: This level is already not Featured.");
    } elseif ($checkType == 2) {
        $deservedCP2 += $featureCP;
        $deservedCP += $featureCP;
    } else {
        $deservedCP += $featureCP;
    }
    if ($result["starEpic"] != 0) {
        if ($checkType == 3) {
            exit("temp_0_Error: This level is already Epic.");
        } elseif ($checkType == -3) {
            $deservedCP2 += $epicCP;
            $deservedCP += $epicCP;
        } else {
            $deservedCP += $epicCP;
        }
    } elseif ($checkType == -3) {
        exit("temp_0_Error: This level is already not Epic.");
    } elseif ($checkType == 3) {
        $deservedCP2 += $epicCP;
        $deservedCP += $epicCP;
    } else {
        $deservedCP += $epicCP;
    }
    if (($checkType == 4 OR $checkType == -4) AND $isMagicSectionManual != 1) {
        exit("temp_0_Error: Manual magic section is not enabled.");
    }
    if ($result["starMagic"] != 0) {
        if ($isMagicSectionManual == 1) {
            if ($checkType == 4) {
                exit("temp_0_Error: This level is already Magic.");
            } elseif ($checkType == -4) {
                $deservedCP2 += $magicCP;
                $deservedCP += $magicCP;
            } else {
                $deservedCP += $magicCP;
            }
        }
    } elseif ($isMagicSectionManual == 1) {
        if ($checkType == -4) {
            exit("temp_0_Error: This level is already not Magic.");
        } elseif ($checkType == 4) {
            $deservedCP2 += $magicCP;
            $deservedCP += $magicCP;
        } else {
            $deservedCP += $starCP;
        }
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
            $query3->execute([':userID' => $gs->getUserID($targetExtID), ':CPShare' => $addCP - $removeCP]);
        }
    }
}
?>