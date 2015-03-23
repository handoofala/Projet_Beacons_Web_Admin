<?php
	session_start();
	header('Content-Type: application/json');
	$inputJSON = file_get_contents('php://input');
	$input= json_decode( $inputJSON, TRUE );
	
	function redirectionErreur404(){
		header('HTTP/1.0 404 Not Found : room not found');
		exit;
	}
	
	function redirectionErreur401($error){
		header('HTTP/1.0 401 Unauthorized : ' . $error);
		exit;
	}
	
	function leaveTchat($token){
		include("../initConnectionBDD.php");
		
        $req = $bdd->prepare("SELECT count(pseudo) AS nbUser, id AS id_user FROM users WHERE token = :token");
        $req->execute(array(':token' => strip_tags($token)));
        $donnees = $req->fetch();
        $req->closeCursor();
        
		if($donnees["nbUser"] == 1){
			$req = $bdd->prepare("DELETE FROM lien_rooms_users WHERE id_user = :id_user");
			$req->execute(array(':id_user' => strip_tags($donnees["id_user"])));
		}else{
			redirectionErreur401();
		}
	}

	function joinTchat($token, $room_id, $passwordRoom){
		include("../initConnectionBDD.php");
		
		$req = $bdd->prepare("SELECT count(id) AS nbId, id FROM users WHERE token = :token");
        $req->execute(array(':token' => strip_tags($token)));
        $donnees = $req->fetch();
        $req->closeCursor();
        
        if($donnees["nbId"] == 1){
			
			$user_id = $donnees["id"];
			$req = $bdd->prepare("SELECT count(id) AS nbId, id FROM rooms WHERE id = :room_id");
			$req->execute(array(':room_id' => strip_tags($room_id)));
			$donnees = $req->fetch();
			$req->closeCursor();
			
			if($donnees["nbId"] == 1){
				$req = $bdd->prepare("SELECT count(id_user) AS nbUser FROM lien_rooms_users WHERE id_user = :id_user");
				$req->execute(array(
					':id_user' => strip_tags($user_id)
				));
				$donnees = $req->fetch();
				$req->closeCursor();
				if($donnees["nbUser"] != 0){
					leaveTchat($token);
				}
				
				$req = $bdd->prepare("SELECT password_room FROM rooms WHERE id = :room_id");
				$req->execute(array(':room_id' => strip_tags($room_id)));
				$donnees = $req->fetch();
				$req->closeCursor();
				
				$psdOk = false;
				if($donnees["password_room"] == NULL OR $donnees["password_room"] == "" OR $donnees["password_room"] == md5($passwordRoom)){
					$psdOk = true;
				}

				if($psdOk){
					$req = $bdd->prepare("INSERT INTO lien_rooms_users VALUES(
						:id_user,
						:id_room,
						'0',
						NOW()
						)
					");
					$req->execute(array(
						':id_user' => strip_tags($user_id),
						':id_room' => strip_tags($room_id)
					));
					$req->closeCursor();
					
					$req = $bdd->prepare("SELECT * FROM messages WHERE id_room = :room_id");
					$req->execute(array(':room_id' => strip_tags($room_id)));
					$i = 0;
					while($donnees = $req->fetch()){
						$dataToSend["messages"][$i]["content"] = $donnees["message"];
						$dataToSend["messages"][$i]["date"] = $donnees["dateTime"];
						$dataToSend["messages"][$i]["user_id"] = $donnees["id_user"];
						$i=$i+1;
					}
					$req->closeCursor();
					
					$req1 = $bdd->prepare("SELECT id_user FROM lien_rooms_users WHERE id_room = :room_id");
					$req1->execute(array(':room_id' => strip_tags($room_id)));
					$i = 0;
					while($donnees1 = $req1->fetch()){
						$req2 = $bdd->prepare("SELECT id, pseudo, isAdmin FROM users WHERE id = :user_id");
						$req2->execute(array(':user_id' => strip_tags($donnees1["id_user"])));
						$donnees2 = $req2->fetch();
						$req2->closeCursor();
						$dataToSend["users"][$i]["id"] = $donnees2["id"];
						$dataToSend["users"][$i]["userName"] = $donnees2["pseudo"];
						$dataToSend["users"][$i]["role"] = $donnees2["isAdmin"];
						$i = $i+1;
					}
					$req1->closeCursor();
					echo json_encode($dataToSend);
				}else{
					redirectionErreur401('Wrong password');
				}
			}else{
				redirectionErreur404();
			}
        }else{
			redirectionErreur401('User unkown');
		}
	}

	if(!isset($input["password"])){
		$input["password"] = NULL;
	}
	
	if(isset($input["token"]) AND isset($input["room_id"])){
		joinTchat($input["token"], $input["room_id"], $input["password"]);
	}else{
		echo "Variables error : token and room_id do not exist";
	}
?>