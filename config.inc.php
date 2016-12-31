<?php
$DbHost = /*DATABASE HOST*/;
$DbUser = /*DATABASE USER*/;
$DbPass = /*DATABASE PASSWORD*/;
$DbName = /*DATABASE NAME*/;

$uploadapi = 'https://uploads.tidepool.org';
$baseurl = 'https://api.tidepool.org';
$loginHash = /*BASE64 of EMAIL:PASSWORD*/;
$deviceId = /*DEXCOM SERIAL*/;

// Set the Local timezone used by xdrip
date_default_timezone_set('America/Los_Angeles');
//Time difference between UTC and Local Timezone
$UTCoffset = -480;
?>