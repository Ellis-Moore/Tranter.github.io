<?php 
/* CONFIGURATION FILE */

/* This file begins the web session and defines variables for further use */

/* VARIABLES */

/*
$user               class      new class started at beginning of session
$user->dbconnect    function   Forms connection to database
*/

session_start();

define('servername', 'localhost');
define('DB', 'tfp');
    
define('username', 'TFP_USER');
define('password', 'eqMWyxTdtVNfhGqm');


//template files
define('welcomePage', 'dashboard.html');

// Development tools
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    
// Initilaize user
$user = new User();

// Make connection
$user->dbConnect(servername, username, password, DB);
    
    
?>