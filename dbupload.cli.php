<?php
$time_start = microtime(true); 
include_once "tidepool.inc.php";
error_reporting(E_ALL);

ConnectDB();
$results = RunQuery("SELECT * FROM `Data` WHERE `Uploaded` IS NULL ORDER BY `Data`.`DateString` ASC LIMIT 250");
if(mysqli_num_rows($results)>0)
{
	Login();
	while ($row = mysqli_fetch_array($results)){
		echo UploadBG($row["DateString"],$row["Type"],$row["Value"],$row["Payload"]);
		ConfirmUpload($row["Payload"]);
	}
	Logout();
}
else
{
   $cronjob = fopen("c:\\xampp\\htdocs\\drip\\cronjob.bat","w");
   fwrite($cronjob, "exit");
   fclose($cronjob);
}
DisconnectDB();
$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
echo PHP_EOL . 'Total Execution Time: '.$execution_time.' secs';
?>
