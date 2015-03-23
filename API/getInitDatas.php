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
			$req = $bdd->prepare("SELECT * FROM rooms WHERE id IN (
				SELECT id_room FROM lien_rooms_ecoles WHERE id_ecole IN (
					SELECT id_ecole FROM lien_users_ecoles WHERE id_user = :id_user
					)
				)
			");
			$req->execute(array(':id_user' => strip_tags($donnees["id"])));
			$i = 0;
			while($donnees = $req->fetch()){
				
				$req2 = $bdd->prepare("SELECT * FROM beacons WHERE id = :id_beacon");
				$req2->execute(array(':id_beacon' => strip_tags($donnees["beacon_id_1"])));
				$donnees1 = $req2->fetch();
				$req2->execute(array(':id_beacon' => strip_tags($donnees["beacon_id_2"])));
				$donnees2 = $req2->fetch();
				$req2->execute(array(':id_beacon' => strip_tags($donnees["beacon_id_3"])));
				$donnees3 = $req2->fetch();
				$req2->execute(array(':id_beacon' => strip_tags($donnees["beacon_id_4"])));
				$donnees4 = $req2->fetch();
				$req2->closeCursor();
				
				$dataToSend["rooms"][$i]["id"] = $donnees["id"];
				$dataToSend["rooms"][$i]["name"] = $donnees["name"];
				$dataToSend["rooms"][$i]["beacons"][0]["id"] = $donnees["beacon_id_1"];
				$dataToSend["rooms"][$i]["beacons"][0]["UUID"] = $donnees1["UUID"];
				$dataToSend["rooms"][$i]["beacons"][1]["id"] = $donnees["beacon_id_2"];
				$dataToSend["rooms"][$i]["beacons"][1]["UUID"] = $donnees2["UUID"];
				$dataToSend["rooms"][$i]["beacons"][2]["id"] = $donnees["beacon_id_3"];
				$dataToSend["rooms"][$i]["beacons"][2]["UUID"] = $donnees3["UUID"];
				$dataToSend["rooms"][$i]["beacons"][3]["id"] = $donnees["beacon_id_4"];
				$dataToSend["rooms"][$i]["beacons"][3]["UUID"] = $donnees4["UUID"];
				$i = $i+1;
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