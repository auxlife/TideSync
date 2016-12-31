<?php
include_once "tidepool.inc.php";
error_reporting(E_ALL);
Login();
ConnectDB();
$results = RunQuery("SELECT * FROM `Data` WHERE `Uploaded` IS NULL ORDER BY `Data`.`DateString` ASC");
while ($row = mysqli_fetch_array($results)){
	echo UploadBG($row["DateString"],$row["Type"],$row["Value"],$row["Payload"]);
	ConfirmUpload($row["Payload"]);
}
DisconnectDB();
Logout();
?>