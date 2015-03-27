<?php
//------------------------------
// Payload data you want to send 
// to Android device (will be
// accessible via intent extras)
//------------------------------

$data = array( 'message' => 'Hello World!' );

//------------------------------
// The recipient registration IDs
// that will receive the push
// (Should be stored in your DB)
// 
// Read about it here:
// http://developer.android.com/google/gcm/
//------------------------------

$ids = array( 'APA91bGcE9e2vZVV5C8ygSLG_7CocauBOVkx558ZUt4uUQ_hvomVjqMrAtO9TFujZZLaYrNkJklSXrN58wBtQpKlQc3EPgED6cFNDY2YBeGamcF9Wk8IkOj7nWdz7UaaTW4jVm__Ac27f4kNyIWe6KJ9XZj5-NutGg' );

//------------------------------
// Define custom GCM function
//------------------------------

function sendGoogleCloudMessage( $data, $ids )
{
    //------------------------------
    // Replace with real GCM API 
    // key from Google APIs Console
    // 
    // https://code.google.com/apis/console/
    //------------------------------

    $apiKey = 'AIzaSyCzYZN0EOIi8YAZ4UdRgrwEERbm6HcNvBc';

    //------------------------------
    // Define URL to GCM endpoint
    //------------------------------

    $url = 'https://android.googleapis.com/gcm/send';

    //------------------------------
    // Set GCM post variables
    // (Device IDs and push payload)
    //------------------------------

	echo "test 1";
    $post = array(
                    'registration_ids'  => $ids,
                    'data'              => $data,
                    );
	echo "test 2";
    //------------------------------
    // Set CURL request headers
    // (Authentication and type)
    //------------------------------

    $headers = array( 
                        'Authorization: key=' . $apiKey,
                        'Content-Type: application/json'
                    );
	echo "test 3";
    //------------------------------
    // Initialize curl handle
    //------------------------------

    $ch = curl_init();
	echo "test 4";
    //------------------------------
    // Set URL to GCM endpoint
    //------------------------------

    curl_setopt( $ch, CURLOPT_URL, $url );
	echo "test 5";
    //------------------------------
    // Set request method to POST
    //------------------------------

    curl_setopt( $ch, CURLOPT_POST, true );
	echo "test 6";
    //------------------------------
    // Set our custom headers
    //------------------------------

    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
	echo "test 7";
    //------------------------------
    // Get the response back as 
    // string instead of printing it
    //------------------------------

    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	echo "test 8";
    //------------------------------
    // Set post data as JSON
    //------------------------------

    curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $post ) );
	echo "test 9";
    //------------------------------
    // Actually send the push!
    //------------------------------

    $result = curl_exec( $ch );
	echo "test 10";
    //------------------------------
    // Error? Display it!
    //------------------------------

    if ( curl_errno( $ch ) )
    {
        echo 'GCM error: ' . curl_error( $ch );
    }
	echo "test 11 if";
    //------------------------------
    // Close curl handle
    //------------------------------

    curl_close( $ch );
	echo "test 12";
    //------------------------------
    // Debug GCM response
    //------------------------------

    echo $result;
	echo "test 13 end";
}

echo "bonjour";
//------------------------------
// Call our custom GCM function
//------------------------------
//phpinfo();
echo "test begin";
sendGoogleCloudMessage(  $data, $ids );
echo "test end;"

?>
