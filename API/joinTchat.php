<?php
	session_start();
	header('Content-Type: application/json');
	$inputJSON = file_get_contents('php://input');
	$input= json_decode( $inputJSON, TRUE );
	
	function sendGoogleCloudMessage( $data, $ids ){
		//------------------------------
		// Replace with real GCM API 
		// key from Google APIs Console
		// 
		// https://code.google.com/apis/console/
		//------------------------------

		//server
		$apiKey = 'AIzaSyCzYZN0EOIi8YAZ4UdRgrwEERbm6HcNvBc';
		//browser
		//$apiKey = 'AIzaSyB5ow0scXluolavFZsvDw-MnRugeUJhVBg';

		//------------------------------
		// Define URL to GCM endpoint
		//------------------------------

		$url = 'https://android.googleapis.com/gcm/send';

		//------------------------------
		// Set GCM post variables
		// (Device IDs and push payload)
		//------------------------------

		$post = array(
						'registration_ids'  => $ids,
						'data'              => $data,
						);

		//------------------------------
		// Set CURL request headers
		// (Authentication and type)
		//------------------------------

		$headers = array( 
							'Authorization: key=' . $apiKey,
							'Content-Type: application/json'
						);

		//------------------------------
		// Initialize curl handle
		//------------------------------

		$ch = curl_init();

		//------------------------------
		// Set URL to GCM endpoint
		//------------------------------

		curl_setopt( $ch, CURLOPT_URL, $url );

		//------------------------------
		// Set request method to POST
		//------------------------------

		curl_setopt( $ch, CURLOPT_POST, true );

		//------------------------------
		// Set our custom headers
		//------------------------------

		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

		//------------------------------
		// Get the response back as 
		// string instead of printing it
		//------------------------------

		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		//------------------------------
		// Set post data as JSON
		//------------------------------

		curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $post ) );

		//------------------------------
		// Actually send the push!
		//------------------------------

		$result = curl_exec( $ch );

		//------------------------------
		// Error? Display it!
		//------------------------------

		if ( curl_errno( $ch ) ){
			echo 'GCM error: ' . curl_error( $ch );
		}

		//------------------------------
		// Close curl handle
		//------------------------------

		curl_close( $ch );

		//------------------------------
		// Debug GCM response
		//------------------------------

		//echo $result;
	}
	
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
						$dataToSend["messages"][$i]["date"] = strtotime($donnees["dateTime"]);
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
						$dataToSend["users"][$i]["pseudo"] = $donnees2["pseudo"];
						$dataToSend["users"][$i]["role"] = $donnees2["isAdmin"];
						$i = $i+1;
					}
					$req1->closeCursor();
					echo json_encode($dataToSend);
					
					$req = $bdd->prepare("SELECT id, pseudo FROM users WHERE token = :token");
					$req->execute(array(':token' => strip_tags($token)));
					$donnees = $req->fetch();
					$req->closeCursor();
					
					$data = array(
						'action' => 'join', 
						'id' => $donnees["id"], 
						'pseudo' => $donnees["pseudo"]
					);
					
					$jdata = json_encode($data);
					$sendData = array( 'message' => $jdata );				

					$req = $bdd->prepare("SELECT id_device FROM devices WHERE id_user IN (SELECT id_user FROM lien_rooms_users WHERE id_room = :room_id)");
					$req->execute(array(':room_id' => strip_tags($room_id)));
					$i = 0;
					while($donnees = $req->fetch()){
						$ids[$i] = $donnees["id_device"];
						$i=$i+1;
					}
					if(isset($ids)){
						sendGoogleCloudMessage( $sendData, $ids );
					}
					$req->closeCursor();
					//data : id du gars qui se co + pseudo + "action" : "join" en json ; ids device_id qui correspond aux users_id qui sont dans la room
					
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
