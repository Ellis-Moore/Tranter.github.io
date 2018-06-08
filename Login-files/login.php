<?php
    // Run required files 
    require_once 'access.php';
    require_once 'config.php';

    // Capture the input
	$request = file_get_contents('php://input');
	
	// Decode the JSON so it is useable in PHP
	$JSONobj = json_decode($request);

    // Create variables in a more recognisable manner 
    $username = $JSONobj->username;
    $password = $JSONobj->password;
    
    if($user->login($username, $password) === true){
        /* send success */
       
        /* Create token for user */
        $token = $user->getToken();
        /* Send back success, Token and Username in an Array */
        $myArr = array("success", $token, $username);
        echo json_encode($myArr);
 
        
    }else{
        /* If the account used doesnt exist */
		if($user->login($username, $password) === false){
			
            /* Send back failure */
			$myArr = array("Unpermitted");
			echo json_encode($myArr);
			/* close connection*/
			die;
		}
		if($user->login($username, $password) == "wrongpass"){
			/* record login attmempt*/

			// Create connection
			$conn = new mysqli("localhost", "TFP_USER", "eqMWyxTdtVNfhGqm", "tfp");

			/* prepare the parameterised */
			$sql = "UPDATE logins SET wrong_logins = wrong_logins + 1 WHERE Username = '".$username."'";

			/* execute the statement */
			$result = $conn->query($sql);

			/* prepare the parameterised */
			$sql = "SELECT wrong_logins FROM logins WHERE Username = '".$username."'";

			/* execute the statement */
            $result = $conn->query($sql);
			// $wronglogins = array();
			$wronglogins = $result->fetch_all(MYSQL_ASSOC);
            var_dump((int)$wronglogins);
            if($wronglogins == "5"){
                /* Send back failure */
                $myArr = array("Unpermitted");
                echo json_encode($myArr);
                /* close connection*/
                die;
            }else{
                /* Send back array with numberof wrong logins and status */
                $myArr = array("wronglogin", $wronglogins);
                echo json_encode($myArr);

                /* Exit script */
                die;
            }
		}

	}

?>