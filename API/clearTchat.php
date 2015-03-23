<?php
	session_start();
	header('Content-Type: application/json');
	$inputJSON = file_get_contents('php://input');
	$input= json_decode( $inputJSON, TRUE );
	
	function redirectionErreur401($error){
		header('HTTP/1.0 401 Unauthorized : ' . $error);
		exit;
	}

	function clearTchat($token){
		include("../initConnectionBDD.php");
		
        $req = $bdd->prepare("SELECT count(id) AS nbId, id, isAdmin FROM users WHERE token = :token");
        $req->execute(array(':token' => strip_tags($token)));
        $donnees = $req->fetch();
        $req->closeCursor();
        
        if($donnees["nbId"] == 1){
			if($donnees["isAdmin"] == 1){
				$req = $bdd->prepare("DELETE FROM messages WHERE id_room = (SELECT id_room FROM lien_rooms_users WHERE id_user = :user_id)");
				$req->execute(array(
					':user_id' => strip_tags($donnees["id"])
				));
				$req->closeCursor();
			}else{
				redirectionErreur401('User is not Admin');
			}
        }else{
			redirectionErreur401('User unknown');
		}
	}

	
	if(isset($input["token"])){
		clearTchat($input["token"]);
	}else{
		echo "Variables error : token does not exist";
	}
?>