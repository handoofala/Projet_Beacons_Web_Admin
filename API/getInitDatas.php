<?php
	session_start();
	header('Content-Type: application/json');
	$inputJSON = file_get_contents('php://input');
	$input= json_decode( $inputJSON, TRUE );
	
	function redirectionErreur401($error){
		header('HTTP/1.0 401 Unauthorized : ' . $error);
		exit;
	}

	function getInitDatas($token){
		include("../initConnectionBDD.php");
		
        $req = $bdd->prepare("SELECT count(id) AS nbId, id FROM users WHERE token = :token");
        $req->execute(array(':token' => strip_tags($token)));
        $donnees = $req->fetch();
        $req->closeCursor();
        
        if($donnees["nbId"] == 1){
			$req = $bdd->prepare("SELECT rooms.id AS id, rooms.name AS name,beacons.id AS idBeacon, beacons.UUID as UUID, beacons.id_room AS relatedRoom FROM rooms
				INNER JOIN beacons
				ON beacons.id_room = rooms.id
				WHERE rooms.id IN (SELECT id_room FROM lien_rooms_ecoles WHERE id_ecole = (SELECT id_ecole FROM lien_users_ecoles WHERE id_user = (SELECT id FROM users WHERE pseudo = :pseudo)))
			");
			$req->execute(array(
				':pseudo' => strip_tags($_SESSION["user"])
			));
									
			$req2 = $bdd->prepare("SELECT count(beacons.id) AS compt FROM rooms
				INNER JOIN beacons
				ON beacons.id_room = rooms.id
				WHERE rooms.id IN (SELECT id_room FROM lien_rooms_ecoles WHERE id_ecole = (SELECT id_ecole FROM lien_users_ecoles WHERE id_user = (SELECT id FROM users WHERE pseudo = :pseudo)))
			");
			$req2->execute(array(
				':pseudo' => strip_tags($_SESSION["user"])
			));
			$donnees2 = $req2->fetch();
			$req2->closeCursor();
			
			$size = $donnees2["compt"];
			$donnees = $req->fetch();
			$basics = 0;
			for($i = 1; $i < $size; $i++){
				$oldName = $donnees["name"];
				$dataToSend["rooms"][$basics]["id"] = $donnees["id"];
				$dataToSend["rooms"][$basics]["name"] = $oldName;
				
				$iTmp = -1;
				do{
					$iTmp++;
					$dataToSend["rooms"][$basics]["beacons"][$iTmp]["id"] = $donnees["idBeacon"];
					$dataToSend["rooms"][$basics]["beacons"][$iTmp]["UUID"] = $donnees["UUID"];
					$oldRoom = $donnees["relatedRoom"];
				}while($donnees = $req->fetch() AND $donnees["relatedRoom"] == $oldRoom);
				$i+=$iTmp;
				$basics++;
			}
			if(!isset($dataToSend)){
				$dataToSend = json_decode ("{\"rooms\":[]}");
			}
			$req->closeCursor();
			echo json_encode($dataToSend);
        }else{
			redirectionErreur401('User unknown');
		}
	}

	
	if(isset($input["token"])){
		getInitDatas($input["token"]);
	}else{
		echo "Variables error : token does not exist";
	}
?>