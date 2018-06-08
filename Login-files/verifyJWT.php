<?php
    // Launch config and secureLogin
    require_once 'access.php';
    require_once 'config.php';

    // Collect request
    $request = file_get_contents('php://input');

    // Decode request and create obj
    $JSONobj = json_decode($request);

    // Separate Token from object
    $token = $JSONobj->token;

    // Verfy token and create boolean ( 1 or 0 )
	$verify = $user->verifyToken($token);
    
    // Check boolean
	if ($verify[0] === TRUE) {
        $verify[0] == "success";
        // Encode verify
        echo json_encode($verify);
		
	}
	else {
		echo("error");
		
	}
?>