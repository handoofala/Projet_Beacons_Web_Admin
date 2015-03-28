<?php
	session_start();
	header('Content-Type: application/json');
	$inputJSON = file_get_contents('php://input');
	$input= json_decode( $inputJSON, TRUE );
	
	function redirectionErreur401($error){
		header('HTTP/1.0 401 Unauthorized : ' . $error);
		exit;
	}

	function setDeviceId($token,$gcmKey){
		include("../initConnectionBDD.php");
		
		echo $gcmKey;
        $req = $bdd->prepare("SELECT count(id) AS nbId, id FROM users WHERE token = :token");
        $req->execute(array(':token' => strip_tags($token)));
        $donnees = $req->fetch();
        $req->closeCursor();


		if($donnees["nbId"] == 1){
			$req = $bdd->prepare("SELECT count(id_device) AS nbIdDevice FROM devices WHERE id_device = :id_device");
			$req->execute(array(':id_device' => strip_tags($gcmKey)));
			$donnees2 = $req->fetch();
			$req->closeCursor();
			
			if($donnees2["nbIdDevice"] == 0){
				$req = $bdd->prepare("INSERT INTO devices VALUES(
					:id_device,
					:id_user
					)
				");
				$req->execute(array(
					':id_device' => strip_tags($gcmKey),
					':id_user' => strip_tags($donnees["id"])
				));
				$req->closeCursor();
			}else{
				redirectionErreur401('wrong token / token unknown');
			}
		}else{
			redirectionErreur401('id_device already exist');
		}
	}

	if(isset($input["token"]) AND isset($input["gcm_key"])){
		setDeviceId($input["token"],$input["gcm_key"]);
	}else{
		echo "Variables error : token or gcm_key does not exist";
	}
?>
