<?php
	session_start();
	include("../initPage.php");
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
	
	function redirectionErreur401(){
		header('HTTP/1.0 409 Conflict : user is not currently in this room.');
		exit;
	}

	function leaveTchat($token){
		include("../initConnectionBDD.php");
		
        $req = $bdd->prepare("SELECT count(pseudo) AS nbUser, id AS id_user FROM users WHERE token = :token");
        $req->execute(array(':token' => strip_tags($token)));
        $donnees = $req->fetch();
        $req->closeCursor();
        
		if($donnees["nbUser"] == 1){
			$req = $bdd->prepare("SELECT count(id_room) AS nbIdRoom, id_room FROM lien_rooms_users WHERE id_user = :id_user");
			$req->execute(array(':id_user' => strip_tags($donnees["id_user"])));
			$room_id = $req->fetch();
			$req->closeCursor();
			
			if($room_id["nbIdRoom"] == 1){
				$req = $bdd->prepare("DELETE FROM lien_rooms_users WHERE id_user = :id_user");
				$req->execute(array(':id_user' => strip_tags($donnees["id_user"])));
				$req->closeCursor();
				
				$req = $bdd->prepare("SELECT id, pseudo FROM users WHERE token = :token");
				$req->execute(array(':token' => strip_tags($token)));
				$donnees = $req->fetch();
				$req->closeCursor();
				
				$data = array(
					'action' => 'leave', 
					'id' => $donnees["id"],
					'pseudo' => $donnees["pseudo"]
				);
				
				$jdata = json_encode($data);
				$sendData = array( 'message' => $jdata );
				
				$req = $bdd->prepare("SELECT id_device FROM devices WHERE id_user IN (SELECT id_user FROM lien_rooms_users WHERE id_room = :room_id)");
				$req->execute(array(':room_id' => strip_tags($room_id["id_room"])));
				$i = 0;
				while($donnees = $req->fetch()){
					$ids[$i] = $donnees["id_device"];
					// $i=$i+1;
				}
				if(isset($ids)){
					sendGoogleCloudMessage( $sendData, $ids );
				}
				$req->closeCursor();
			}
		}else{
			redirectionErreur401();
		}
	}

	if(isset($input["token"])){
		leaveTchat($input["token"]);
	}else{
		echo "Variable error : token does not exist";
	}
?>