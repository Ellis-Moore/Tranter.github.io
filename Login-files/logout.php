<?php
    /*  
    *   This document accesses the user class to log a user out 
        and should only be accessed via the use of the logout button
        else an error will occur
        
    *   Algorithm 
        
        CAPTURE INPUT 
        
        IF INPUT is TRUE THEN
            Access user class and use logout method
        ELSE
            Error message
        END IF 
        
    */

    // Run additional PHP documents
    require_once 'access.php';
    require_once 'config.php';

    // Capture the input
	$request = file_get_contents('php://input');
	
	// Decode the JSON so it is useable in PHP
	$JSONobj = json_decode($request);
    
    // Paramertise input 
    $request = $JSONobj->logout;
    
    // Check input
    if($request === true){
        // log user out
        $user->logout();
        
        // send back success
        echo json_encode("success");

        // Exit script
        die;
    }else{
        // Error, exit script
        die("Connection failed: " . $conn->connect_error);
    }
    

?>