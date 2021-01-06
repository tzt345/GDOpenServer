<?php
class levelAPI {
    function Select(){
        require "../incl/lib/connection.php";
        require_once "../incl/lib/exploitPatch.php";
        $ep = new exploitPatch();
        if (!empty($_GET["levelID"])) {
            $response = $_GET["levelID"];
        } elseif(!empty($_POST["levelID"])) {
            $response = $_POST["levelID"];
        }
        $levelID = $ep->remove($response);
        $levels = array();
        $data = $db->prepare('SELECT * FROM levels WHERE levelID = :levelID');
        $data->execute(['levelID' => $levelID]);
        while ($OutputData = $data->fetch(PDO::FETCH_ASSOC)) {
            $desc = base64_decode($OutputData['levelDesc']);
            $levels = array(
                'levelName' => $OutputData['levelName'],
                'levelID' => $OutputData['levelID'],
                'creator' => $OutputData['userName'],
                'LevelVersion' => $OutputData['levelVersion'],
                'levelLength' => $OutputData['levelLength'],
                'Downloads' => $OutputData['downloads'],
                'Likes' => $OutputData['likes'],
                'LevelPassword' => $OutputData['password'],
                'objects' => $OutputData['objects'],
                'coins' => $OutputData['coins'],
                'verifiedCoins' => $OutputData['starCoins'],
                'songID' => $OutputData['songID'],
                'stars' => $OutputData['starStars'],
                'featured' => $OutputData['starFeatured'],
                'epic' => $OutputData['starEpic'],
                'description' => $desc
            );
        }
        return json_encode($levels);
    }
}

$API = new levelAPI;
header('Content-Type: Application/json');
echo $API->Select();
?>