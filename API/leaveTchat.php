<?php
	session_start();
	include("../initPage.php");
	header('Content-Type: application/json');
	
	function redirectionErreur401(){
		header('HTTP/1.0 401 Unauthorized : user have not the right to post message here');
		exit;
	}

	function leaveTchat($token, $id_room){
        $req = $bdd->prepare("SELECT count(pseudo) AS user FROM users WHERE pseudo = :pseudo");
        $req->execute(array(':pseudo' => strip_tags($token)));
        $donnees = $req->fetch();
        $req->closeCursor();
        
		if($donnees["pseudo"] == 1){
			$req = $bdd->prepare("DELETE FROM lien_rooms_users WHERE id_user = (SELECT id FROM users WHERE pseudo = :pseudo) AND id_room = :id_room");
			$req->execute(array(
				':pseudo' => strip_tags($token),
				':id_room' => strip_tags($id_room)
			));
		}else{
			redirectionErreur401();
		}
	}

if(isset($_POST["data"])){
	$jsonData = json_decode($_POST["data"]);
	leaveTchat(jsonData.get("token"), jsonData.get("id_room"));
}else{
	echo "Variable error : $_POST[\"data\"] does not exist";
}
?>