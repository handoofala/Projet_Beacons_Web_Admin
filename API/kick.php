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
				$req = $bdd->prepare("SELECT count(id_user) AS nbUser, pseudo FROM lien_rooms_users WHERE id_user = :user_id");
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
					
					$data = array(
						'action' => 'kick',
						'id' => strip_tags($userIdToKick),
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