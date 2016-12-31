<?php
include_once "db.inc.php";
if(!set_time_limit(0))
	die("Could not set infinite script timeout.");
error_reporting(E_ALL);
ini_set('display_errors', 'On');
$sessionStart = date(DATE_ATOM);
$sessionToken = FALSE;
$sessionData = array();


function CheckFile($filename) {
    if(!file_exists($filename))
		touch($filename);
	return TRUE;
}

function Kill($string) {
	echo '<h1><font color="red">Fatal Error!!</font><h1><br>'.$string;
	die();
}

function ConvertToMgDl($value) {
	return $value * 18.018018;
}

function ConvertToMmol($value) {
	return $value / 18.018018;
}

function GetDeviceTime($value) {
	$DT = strtotime($value);
	return date('Y-m-d',$DT).'T'.date('H:i:s',$DT);
}

function GetUtcTime($value) {
	$DT = strtotime($value);
	$DT = $DT + ($GLOBALS['UTCoffset'] * -60);
	return date('Y-m-d',$DT).'T'.date('H:i:s',$DT).".000Z";
}

function GetUtcOffset() {
	return $GLOBALS['UTCoffset'];
}

function DownloadAllData() {	// not working
	$Querystring = 'METAQUERY WHERE emails CONTAINS ' . implode($GLOBALS['sessionData']['emails']) . 'QUERY TYPE IN basal, bolus, cbg, cgmSettings, deviceEvent, deviceMeta, pumpSettings, settings, smbg';
	$url = $GLOBALS['baseurl'] . '/query/data';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-tidepool-session-token: ' . $GLOBALS['sessionToken'],'Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $Querystring);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	file_put_contents("data_download.json", $response);
	return $response;
}

function UploadBG($date, $type, $value, $payload) {
	if($type == "sgv") {
		$JSONstring = '{"uploadId":"'.GetUploadID().'","deviceId":"'.$GLOBALS['deviceId'].'","deviceTime":"'.GetDeviceTime($date).'","time":"'.GetUtcTime($date).'","timezoneOffset":'.$GLOBALS['UTCoffset'].',"type":"cbg","units":"mmol/L","value":'.ConvertToMmol($value).','.$payload.'}';
		return UploadData($JSONstring);
	}
	if($type == "mbg") {
		$JSONstring = '{"uploadId":"'.GetUploadID().'","deviceId":"'.$GLOBALS['deviceId'].'","deviceTime":"'.GetDeviceTime($date).'","time":"'.GetUtcTime($date).'","timezoneOffset":'.$GLOBALS['UTCoffset'].',"type":"smbg","subType":"manual","units":"mmol/L","value":'.ConvertToMmol($value).','.$payload.'}';
		return UploadData($JSONstring);
	}
	
}

function UploadData($JSONstring) {
	$url =  $GLOBALS['uploadapi'].'/data';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-tidepool-session-token: ' . $GLOBALS['sessionToken'],'Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $JSONstring);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	if(GetHttpStatus($response) != 200)
	{
		Kill("Sorry the upload failed.<br>Request:<br>".$JSONstring."<br>Response:<br>".$response);
	}
	return TRUE;
}

function GetGroupIds(){ // needs repair
	$url = $GLOBALS['baseurl'] . '/access/groups/' . $GLOBALS['sessionData']['userid'];
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-tidepool-session-token: ' . $GLOBALS['sessionToken']));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	return $response;
}

function GetUploadID() {
    return 'upid_' . substr(md5($GLOBALS['deviceId'] . '_' . $GLOBALS['sessionStart']),0, 12);
}

function GetHttpStatus($string) {
	$StatusStart = 'HTTP/1.1 ';
	$StatusEnd = PHP_EOL;
	return SubString($string, $StatusStart, $StatusEnd);
}

function ExtractToken($string) {
	$TokenStart = 'x-tidepool-session-token: ';
	$TokenEnd = PHP_EOL;
	return SubString($string, $TokenStart, $TokenEnd);
}

function GetJSON($string) {
	$JSONStart = '{';
	$JSONEnd = '}';
	return '{' . SubString($string, $JSONStart, $JSONEnd) . '}';
}

function SubString($string, $start, $end) {
	$string =  substr($string, strpos($string, $start) + strlen($start));
	return substr($string,0,strpos($string, $end));
}

function IsTokenValid() {
	if($GLOBALS['sessionToken'] != FALSE)
	{
		$url = $GLOBALS['baseurl'] . '/auth/login';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-tidepool-session-token: ' . $GLOBALS['sessionToken']));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		if(GetHttpStatus($response) == 200)
		{
			$GLOBALS['sessionStart'] = date(DATE_ATOM);
			return TRUE;
		}
		$GLOBALS['sessionToken'] = FALSE;
		return FALSE;
	}
	return FALSE;
}

function LoadToken() {
	if(!file_exists("tidepool.auth"))
		return FALSE;
	$response = file_get_contents("tidepool.auth");
	$GLOBALS['sessionData'] = json_decode(GetJSON($response), true);
	$GLOBALS['sessionToken'] = ExtractToken($response);
	$GLOBALS['sessionStart'] = date(DATE_ATOM);
	if(!IsTokenValid())
	{
		unlink("tidepool.auth");
		return FALSE;
	}
	return TRUE;
}

function Login() {
	if($GLOBALS['sessionToken'] == FALSE)
	{
		if(LoadToken())
			return TRUE;
		$url = $GLOBALS['baseurl'] . '/auth/login';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $GLOBALS['loginHash']));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		if(GetHttpStatus($response) != 200)
		{
			Kill("Sorry the login failed.<br>Response:<br>".$response);
		}
		$GLOBALS['sessionData'] = json_decode(GetJSON($response), true);
		$GLOBALS['sessionToken'] = ExtractToken($response);
		$GLOBALS['sessionStart'] = date(DATE_ATOM);
		file_put_contents("tidepool.auth", $response);
		return TRUE;
	}
	if(IsTokenValid())
		return TRUE;
	else
		return Login();
	return FALSE;
}

function Logout() {
		LoadToken();
		$url = $GLOBALS['baseurl'] . '/auth/logout';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-tidepool-session-token: ' . $GLOBALS['sessionToken']));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		if(GetHttpStatus($response) != 200)
		{
			Kill("Sorry the logout failed.<br>Response:<br>".$response);
		}
		$GLOBALS['sessionToken'] = FALSE;
		unlink("tidepool.auth");
		return TRUE;
}

function GetToken() {
	return $GLOBALS['sessionToken'];
}
?>
