<?php
include_once "tidepool.inc.php";
error_reporting(E_ALL);

function sortByDate($a, $b) {
    return $a['date'] - $b['date'];
}
function RandomString($Length)
{
    $characters = str_split('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',1);
    $randstring = array();
    for ($i = 0; $i < $Length; $i++) {
        $randstring[] = $characters[rand(0, count($characters)-1)];
    }
    return implode($randstring);
}
rename("api/v1/dexcom.data" , "api/v1/data.old");
$file = file_get_contents("api/vvv1/data.old");
$file = str_replace('[','',$file);
$file = explode("]",$file);
$json = array();
$data = array();
foreach ($file as $point) {
	$json = explode("},",$point);
	foreach ($json as $value) {
		$value = $value."}";
		if(substr( $value, 0, 25 ) === '{"device":"xDrip-DexcomG5'){
			$data[] = json_decode($value, true);
		}
	}
}
unset($file,$json);
$array = array();
foreach ($data as $entry) {
	if($entry["type"] == "sgv"){
		switch($entry["noise"]){
			case 0:
				$entry["noise"] = "NONE";
			break;
			case 1:
				$entry["noise"] = "CLEAN";
			break;
			case 2:
				$entry["noise"] = "LIGHT";
			break;
			case 3:
				$entry["noise"] = "MEDIUM";
			break;
			case 4:
				$entry["noise"] = "HEAVY";
			break;
			case 6:
				$entry["noise"] = "MAX";
			break;
			default:
				$entry["noise"] = "NOT_COMPUTED";
		}
		$array[$entry["dateString"].$entry["type"].$entry["sgv"]] = $entry;
	}
	if($entry["type"] == "mbg")
		$array[$entry["dateString"].$entry["type"].$entry["mbg"]] = $entry;
}
unset ($data);

usort($array, 'sortByDate');
ConnectDB();
foreach ($array as $entry) {
	if($entry["type"] == "sgv")
		echo InsertValue($entry["dateString"], $entry["type"], $entry["sgv"] , '"payload":{"internalTime":"'.GetDeviceTime($entry["dateString"]).'","delta":'.$entry["delta"].',"noiseMode":"'.$entry["noise"].'","trend":"'. $entry["direction"].'","value":'.ConvertToMmol($entry["sgv"]).'}');
	if($entry["type"] == "mbg")
		echo InsertValue($entry["dateString"], $entry["type"], $entry["mbg"] , '"payload":{"subType":"manual","time":"'.GetUTCTime($entry["dateString"]).'","timezoneOffset":'.GetUtcOffset().',"type":"smbg","units":"mmol/L","value":'.ConvertToMmol($entry["mbg"]).'}');
}
DisconnectDB();
rename("api/v1/data.old", "api/v1/olddata/".RandomString(12).".".RandomString(4));
/*
GetDeviceTime($entry["dateString"])
*/
//ConnectDB();
//echo InsertValue($entry["dateString"], $entry["type"], $entry["mbg"] , "Payload");
//echo ConfirmUpload("Payload");
//DisconnectDB();

//'"payload":{"internalTime":"'.GetDeviceTime($entry["dateString"]).'","noiseMode":"Clean","trend":"'. $entry["direction"].'","value":'.ConvertToMmol($entry["sgv"]).'}'
//"payload":{"subType":"manual","time":"'.GetUTCTime($entry["dateString"]).'","timezoneOffset":'.GetUtcOffset().',"type":"smbg","units":"mmol/L","value":'.ConvertToMmol($entry["sgv"]).'}
?>
