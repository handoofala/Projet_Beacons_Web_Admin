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
	
	function redirectionErreur409($error){
		header('HTTP/1.0 409 Conflict : ' . $error);
		exit;
	}

	function postMsg($token, $msg){
		include("../initConnectionBDD.php");
		
        $req = $bdd->prepare("SELECT count(id) AS nbUserId, id, pseudo FROM users WHERE token = :token");
        $req->execute(array(':token' => strip_tags($token)));
        $donnees = $req->fetch();
		$pseudo = $donnees["pseudo"];
		$id_user = $donnees["id"];
        $req->closeCursor();
        
		if($donnees["nbUserId"] == 1){
			$id_user = $donnees["id"];
			
			$req = $bdd->prepare("SELECT count(id_room) AS nbRoom, id_room, is_kicked FROM lien_rooms_users WHERE id_user = :id_user");
			$req->execute(array(
				':id_user' => strip_tags($id_user)
			));
			$donnees = $req->fetch();
			$room_id = $donnees["id_room"];
			$req->closeCursor();
			
			if($donnees["nbRoom"] == 1 OR $donnees["is_kicked"] == 1){
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
				
				$data = array(
					'action' => 'post', 
					'id' => $id_user, 
					'pseudo' => $pseudo,
					'content' => strip_tags($msg),
					'dateTime' => time()
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
			}else{
				redirectionErreur409('user not connected on any room or kicked');
			}
		}else{
			redirectionErreur409('Unknown user');
		}
	}

	if(isset($input["token"]) AND isset($input["content"])){
		postMsg($input["token"], $input["content"]);
	}else{
		echo "Variables error : token and content do not exist";
	}
?>