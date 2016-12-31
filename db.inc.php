<?php
ini_set ('error_reporting', E_ALL);
ini_set ('display_errors', '1');
error_reporting (E_ALL|E_STRICT);
include_once "config.inc.php";


$dbc = mysqli_init();  

function ConnectDB() {
	$GLOBALS['dbc'] = mysqli_init();  
	mysqli_ssl_set($GLOBALS['dbc'],'dbcrt/client-key.pem', 'dbcrt/client-cert.pem', 'dbcrt/ca-cert.pem', '/dbcrt/ca.pem', NULL) ; 
	mysqli_real_connect($GLOBALS['dbc'], $GLOBALS['DbHost'], $GLOBALS['DbUser'], $GLOBALS['DbPass'], $GLOBALS['DbName'], 3306, NULL, MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT); 
	if (!$GLOBALS['dbc'])
		die('Could not connect to MySQL: ' . mysqli_connect_error() );      
	else
		mysqli_set_charset($GLOBALS['dbc'], 'utf8');  
	return TRUE;
}
function DisconnectDB() {
	return mysqli_close($GLOBALS['dbc']);
}
function InsertValue($DateString, $Type, $Value, $Payload) {
	$query = "INSERT INTO `Data` (`Added`,`DateString`, `Type`, `Value`, `Payload`, `Uploaded`) VALUES (CURRENT_TIMESTAMP(),'".$DateString."', '".$Type."', ".$Value.", '".$Payload."', NULL);";
	return mysqli_query($GLOBALS['dbc'],$query);
}
function ConfirmUpload($Payload) {
	$query = "UPDATE `Data` SET `Uploaded` = '".date('Y-m-d H:i:s')."' WHERE `Data`.`Payload` = '".$Payload."'";
	return mysqli_query($GLOBALS['dbc'],$query);
}

function RunQuery($query) {
	return mysqli_query($GLOBALS['dbc'],$query);
}
?>