<?php
class levelAPI {
    function Select(){
        include "../incl/lib/connection.php";
        require_once "../incl/lib/mainLib.php";
        require_once "../incl/lib/exploitPatch.php";
        $ep = new exploitPatch();
        $mainLib = new mainLib();
        if(isset($_GET["levelID"])) {
            $response = $_GET["levelID"];
        } elseif(isset($_POST["levelID"])) {
            $response = $_POST["levelID"];
        }
        $levelID = $ep->remove($response);
		if(file_exists("../data/levels/$levelID")){
			$levelstring = file_get_contents("../data/levels/$levelID");
        }
        $levels = array();
        $data = $db->prepare('SELECT * FROM levels WHERE levelID = :levelID');
        $data->execute(['levelID' => $levelID]);
        while($OutputData = $data->fetch(PDO::FETCH_ASSOC)){
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
                'String' => $levelstring,
                'extraString' => $OutputData['extraString'],
                'description' => $desc
            );
        }
        return json_encode($levels);
    }
}

$API = new levelAPI();
header('Content-Type: Application/json');
echo $API->Select();
?>