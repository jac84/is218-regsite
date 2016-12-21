<?php

ob_start();
session_start();

date_default_timezone_set('America/New_York'); //set db access timezone

/*database credentials*/
define('DBHOST','sql1.njit.edu');
define('DBUSER','jac84');
define('DBPASS','Aa9v010j');
define('DBNAME','jac84');

session_save_path();

try {
	$db = new PDO("mysql:host=".DBHOST.";dbname=".DBNAME, DBUSER, DBPASS);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo '<p class="bg-danger">'.$e->getMessage().'</p>';
    exit;
}

include('classes/user.php');
//$user = new User($db);
?>
