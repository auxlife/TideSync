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


  class MyDB extends SQLite3
   {
      function __construct()
      {
         $this->open('xdrip.sqlite');
      }
   }
   $db = new MyDB();
   if(!$db){
      echo $db->lastErrorMsg();
   } else {
      echo "Opened database successfully<br><br>\n\n";
   }
   ConnectDB();
	$OldTimeStamp = GetLastTimeStamp();
   $sql ="SELECT * from Calibration Where Timestamp > ".$OldTimeStamp." Order by Timestamp DESC;";

   $ret = $db->query($sql);
   while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
  	  echo InsertValue(GetDeviceTimeFromMU($row['timestamp']), "mbg", $row['bg'] , '"payload":{"subType":"manual","time":"'.GetDeviceTimeFromMU($row['timestamp']).'","timezoneOffset":'.GetUtcOffset().',"type":"smbg","units":"mmol/L","value":'.ConvertToMmol($row['bg']).'}',$row['timestamp']);
	  
   }
 
   $sql ="SELECT * from BgReadings Where Timestamp > ".$OldTimeStamp." Order by Timestamp DESC;";

   $ret = $db->query($sql);
   while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
	  echo InsertValue(GetDeviceTimeFromMU($row['timestamp']), "sgv", round($row['calculated_value']) , '"payload":{"internalTime":"'.GetDeviceTimeFromMU($row['timestamp']).'","value":'.ConvertToMmol(round($row['calculated_value'])).'}',$row['timestamp']);
	  
   }
   $cronjob = fopen("c:\\xampp\\htdocs\\drip\\cronjob.bat","w");
   fwrite($cronjob, "c:\\xampp\\php\\php.exe c:\\xampp\\htdocs\\drip\\dbupload.cli.php". PHP_EOL);
   fwrite($cronjob, "exit");
   fclose($cronjob);
   
   
DisconnectDB();
   $db->close();
?>
