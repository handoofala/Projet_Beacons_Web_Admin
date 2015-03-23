<?php
	session_start();
	header('Content-Type: application/json');
	$inputJSON = file_get_contents('php://input');
	$input= json_decode( $inputJSON, TRUE );
	
	function redirectionErreur401($error){
		header('HTTP/1.0 401 Unauthorized : ' . $error);
		exit;
	}
	
	function redirectionErreur404($error){
		header('HTTP/1.0 404 Not Found : ' . $error);
		exit;
	}

	function kick($token, $userIdToKick, $time){
		include("../initConnectionBDD.php");
		
        $req = $bdd->prepare("SELECT count(id) AS nbId, id, isAdmin FROM users WHERE token = :token");
        $req->execute(array(':token' => strip_tags($token)));
        $donnees = $req->fetch();
        $req->closeCursor();
        
        if($donnees["nbId"] == 1){
			if($donnees["isAdmin"] >= 1){
				$req = $bdd->prepare("SELECT count(id_user) AS nbUser FROM lien_rooms_users WHERE id_user = :user_id");
				$req->execute(array(
					':user_id' => strip_tags($userIdToKick)
				));
				$donnees = $req->fetch();
				$req->closeCursor();
				if($donnees["nbUser"] == 1){
					$time = $time;
					$req = $bdd->prepare("UPDATE lien_rooms_users SET is_kicked = 1, duration = DATE_ADD(NOW(), INTERVAL :time MINUTE) WHERE id_user = :user_kicked");
					$req->execute(array(
						':time' => strip_tags($time),
						':user_kicked' => strip_tags($userIdToKick)
					));
				}else{
					redirectionErreur404('User to kick is not in the room');
				}
			}else{
				redirectionErreur401('User is not Admin');
			}
        }else{
			redirectionErreur401('User unknown');
		}
	}

	
	if(isset($input["token"]) AND isset($input["user_id"]) AND isset($input["duration"])){
		kick($input["token"], $input["user_id"], $input["duration"]);
	}else{
		echo "Variables error : token, user_id and duration do not exist";
	}
?>