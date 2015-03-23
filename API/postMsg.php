<?php
	session_start();
	header('Content-Type: application/json');
	$inputJSON = file_get_contents('php://input');
	$input= json_decode( $inputJSON, TRUE );
	
	function redirectionErreur409(){
		header('HTTP/1.0 409 Conflict : Unknown token or user not connected on any room');
		exit;
	}

	function postMsg($token, $msg){
		include("../initConnectionBDD.php");
		
        $req = $bdd->prepare("SELECT count(id) AS nbUserId, id AS id_user FROM users WHERE token = :token");
        $req->execute(array(':token' => strip_tags($token)));
        $donnees = $req->fetch();
        $req->closeCursor();
        
		if($donnees["nbUserId"] == 1){
			$id_user = $donnees["id_user"];
			
			$req = $bdd->prepare("SELECT count(id_room) AS nbRoom, id_room FROM lien_rooms_users WHERE id_user = :id_user");
			$req->execute(array(
				':id_user' => strip_tags($id_user)
			));
			$donnees = $req->fetch();
			$req->closeCursor();
			
			if($donnees["nbRoom"] == 1){
				$req = $bdd->prepare("INSERT INTO messages VALUES(
					:id_user,
					:id_room,
					:message,
					NOW()
					)
				");
				$req->execute(array(
					':id_user' => strip_tags($id_user),
					':id_room' => strip_tags($donnees["id_room"]),
					':message' => strip_tags($msg)
				));
				$req->closeCursor();
			}else{
				redirectionErreur409();
			}
		}else{
			redirectionErreur409();
		}
	}

	if(isset($input["token"]) AND isset($input["content"])){
		postMsg($input["token"], $input["content"]);
	}else{
		echo "Variables error : token and content do not exist";
	}
?>