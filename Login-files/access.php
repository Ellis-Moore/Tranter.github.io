<?php
    header("Content-Type: application/json; charset=UTF-8");
    date_default_timezone_set('Europe/London');

	class user{
			/** Object $conn being a copy of MYSQL connection */ 
			private $conn;
			/** Object of loegged in user */
			private $user;
			/** Error message string */
			private $msg;
			/** Amount of attempts permitted of wrong attempts (int value) */
			public $permittedAttempts = 5;

			public function dbConnect($server, $user, $pass, $database){
            /**
            * Connection init function
            * @param string $server DB server.
            * @param string $database DB database.
            * @param string $user DB user.
            * @param string $pass DB password.
            *
            * @return bool Returns connection success.
            */
				if(session_status() === PHP_SESSION_ACTIVE){
                    // Attempt connecting to server
					try{
						$conn = new mysqli($server, $user, $pass, $database);
						$this->conn = $conn;
						return true;
                    // If connection fails
					}catch(Exception $e) {
						$this->msg = 'Connection failed.';
						return false;
					}
				}
            }
	
	
	public function getUser(){
        // Method to access object
        return $this->user;
    }
	
	public function login($username,$password){
		/*
		* Login function
		* @param $username, string, user login name 
		* @param $password, string, user entered password
		*
		* function returns boolean depending on success
		
		* VARIABLES
		*  $conn	object	connection object to the database
		*  $stmt	object	holds prepared SQL statements
		*/
		
		/* Check is connection is active */
		if(is_null($this->conn)){
			$this->msg = 'Connection inactive';
			return false; 
		}else{
			/* capture connection */
			$conn = $this->conn;
			
			/* prepare the parameterised */
			$stmt = $conn->prepare(
					"SELECT landlordID, landlordName, Username, Password, wrong_logins 
					 FROM logins
					 WHERE Username = '". $username ."'");
			
			
	
			/* execute the statement */
			$stmt->execute();
			
            /* get results and give parameters */
            $stmt->bind_result($id, $landlordName, $username, $pword, $wrong_logins);
            
			/* pull the user record */
			$user = $stmt->fetch();
            
			
			/* check password */
            if(sha1($password) == $pword){
				/** check if the user has run out of login attempts */
				if($wrong_logins <= $this->permittedAttempts){
                    /** assign user details to object */
					$this->user = $user;
					
					/** add SERVER SIDE session variables for this login*/
                    session_regenerate_id();
                    $_SESSION['id'] = $id;
                    $_SESSION['uname'] = $username;
         
                 
         
					$this->msg = "logged in";
                    return true;
                }else{
					/** set message for exceeding nummber of login attempts*/
                    $this->msg = 'error: This user account is blocked, please contact our support department.';
                    return false;
                }
            }else{
				

				/* pull the user record */
				$this->msg = 'error: Invalid login information or the account is not activated.';
                 
                return "wrongpass";
            } 
			
			/* close statement */
			$stmt->close(); 
        }
    }
        
    public function printMsg(){
        /* 
        * Print error msg function
        */
        
        print $this->msg;
    }

    public function logout(){
        /* 
        * log user out and remove from session
        */
        
        $_SESSION['User'] = null;
        session_regenerate_id();
        return true;
    }
        
    public function getToken() {
		/**
		* JWT token generator
		* @return string $jwtToken base64 encoded string
		*	
		*  VARIABLES
		*   $header          ASSOC array   holds the header for the token
		*   $jsonHeader      JSON object   holds the header for the token in JSON format 
		*   $encodedHeader   base64 string holds the encoded header for the token
		*
		*   $payload         ASSOC array   holds the payload for the token
		*   $jsonPayload     JSON object   holds the payload for the token in JSON format 
		*   $encodedPayload  base64 string holds the encoded payload for the token
		*
		*   $digest          base64 String holds the combined header and payload
		*   $signature       base64 string holds the base64 encoded encrypted digest 
		*   $jwtToken        base64 string holds combined digest and signature 		*/
		
		/** compose the header */
		$header = array();
		$header["alg"] = "HS256";
		$header["typ"] = "JWT";
		
		$jsonHeader = json_encode($header);
		$encoded_header = base64_encode($jsonHeader);
		
		/** grab the user details and compose the payload*/
		$payload = array();
		$payload["userId"] = $_SESSION['id'];
		$payload["loginDate"] = date("d-m-y");
		$payload["loginTime"] = date("h:i:sa");
        $payload["username"] = $_SESSION['uname'];
		
		$jsonPayload = json_encode($payload);
		$encoded_payload = base64_encode($jsonPayload);

		/** combine the header and the payload */
		$digest = $encoded_header . '.' . $encoded_payload;

		$secret_key = "secretkey";
		
		/** create the signature */
		$signature = base64_encode(hash_hmac('sha256', $digest, $secret_key, true));

		/** add the signature to create the token */
		$jwt_token = $digest . '.' . $signature;

		return $jwt_token;	
	}
        
    public function verifyToken($token) {
    /**
    * JWT token verifier
    * @param  string  $token JWT token to verify
    * @return boolean 
    *	
    *  VARIABLES
    *   $tokenValues     ASSOC array     holds the parts of the token
    *   $tokenSignature  base64 string   holds the base64 encode and encypted signature 
    *   $tokenDigest     base64 string   holds the encoded header and payload of the token
    *   $payload         base64 string   holds the payload for the token
    *   $jsonPayload     JSON object     holds the payload for the token in JSON format 
    *   $key             string          holds the key for the payload 
    *   $value           string          holds the value for the payload
    */

    // Split a string by '.' to give the parts of the token
    // $tokenValues[0] is the base64 encoded header
    // $tokenValues[1] is the base64 encoded payload
    // $tokenValues[2] is the base64 encoded and encrpyted signature

    $tokenValues = explode('.', $token);

    // extract the signature from the original token 
    $tokenSignature = $tokenValues[2];

    // concatenating the first two arguments of the $jwt_values array, representing the header and the payload
    $tokenDigest = $tokenValues[0] . '.' . $tokenValues[1];

    // creating the Base 64 encoded new signature generated by applying the HMAC method to the concatenated header and payload values
    $calculatedSignature = base64_encode(hash_hmac('sha256', $tokenDigest, "secretkey", true));

    // checking if the created signature is equal to the received signature
    if($calculatedSignature == $tokenSignature) {
        $jsonPayload  = base64_decode($tokenValues[1]);
        $payload = json_decode($jsonPayload);
        foreach($payload as $key => $value) {
            // echo($key . ": " . $value . "   ");
        }
        // If everything worked fine, if the signature is ok and the payload was not modified you should get a success message
        $myArr = array(true, $payload);
        return $myArr;
    }
    else {
        return false;
    }
		
		
	}
        
    // Add new user to the database
    public function newUser($name, $username, $password){
        /*
        * @param  string  $name     Name of new user 
        * @param  string  $username Username of the new user
        * @param  string  $password Password of new user 
        * @return boolean 
        *	
        *  VARIABLES
        *  $conn	 object	 connection object to the database
		*  $sql   	 object	 holds prepared SQL statements
        *  $hashword string  Encrypted version of $password using sha1() function  
        */

            /* capture connection */
			$conn = $this->conn;
        
        	$hashword = sha1($password);
            // build SQL DML Statement
            $sql = $conn->prepare("INSERT INTO landlords (landlordID,landlordName, landlordUsername, landlordPassword, wrong_logins) 
                    VALUES (NULL, '". $name ."','". $username ."','". $hashword ."',0)");
            
            /* execute the statement */
			$sql->execute();
            
            /* Return boolean */
            return true;
        }
        
    // Select all users from database
    public function allUsers(){
            /*
            * @return array 
            *	
            *  VARIABLES
            *  $conn	 object	 connection object to the database
            *  $sql   	 object	 holds prepared SQL statements
            *  $result   object  contains details of all users
            *  $users    array   contains all $results paramertised 
            */
            /* capture connection */
			$conn = $this->conn;
        
        	
            // build SQL DML Statement
            $sql = "SELECT landlordID, landlordName, landlordUsername, landlordPassword, wrong_logins  FROM landlords WHERE 1";
            
        
            $result = $conn->query($sql);
            $users = array();
            $users = $result->fetch_all(MYSQL_ASSOC);
        
           
            return $users;
        }
        
    // Remove user from database
    public function removeUser($ID){
        /*
        * @return boolean
        *	
        *  VARIABLES
        *  $conn	 object	 connection object to the database
        *  $sql   	 object	 holds prepared SQL statements
        *  $result   boolean 
        */
        /* capture connection */
        $conn = $this->conn;
        
        $sql = "DELETE FROM landlords WHERE landlordID = ". $ID ."";
        
        /* execute the statement */
        $result = $conn->query($sql);
            
        return true;
    }
        
    // Execute SQL
    public function execute($sql){
        $conn = $this->conn;
        
        $result = $conn->query($sql);
        
        return $result;
    }
    
    // Close connection
    public function close(){
        $conn = $this->conn;
        $conn->close();
    }
        

}
    
?>