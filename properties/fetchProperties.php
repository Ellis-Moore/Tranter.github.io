<?php
	header("Content-Type: application/json; charset=UTF-8");
    
    require_once '../Login-files/access.php';
    require_once '../Login-files/config.php';
    
    $conn = $user->dbConnect(servername, username, password, DB);

    // Capture the input
	$request = file_get_contents('php://input');
	
	// Decode the JSON so it is useable in PHP
	$JSONobj = json_decode($request);
	
	// build SQL DML Statement
	if ($JSONobj->address == "") {
        $sql = "SELECT *
                FROM `properties`
                WHERE 1
				ORDER BY streetAddress";
        
    }
	if ($JSONobj->address != ""){

         $sql = "SELECT *
                FROM `tenants`
                WHERE `streetAddress` LIKE '". $JSONobj->address ."%'";
		
	}

	$result = $user->execute($sql);
	$property = array();
	$property = $result->fetch_all(MYSQL_ASSOC);

	echo json_encode($property);
    
	$user->close();

?>