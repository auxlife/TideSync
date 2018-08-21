<?php
$filename = "dexcom.data";
if (!file_exists($filename))
		touch($filename);
file_put_contents($filename, file_get_contents("php://input"), FILE_APPEND | LOCK_EX);
?>
