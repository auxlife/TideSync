<?php
if(isset($_FILES["zip_file"]["name"])) {
	$filename = $_FILES["zip_file"]["name"];
	$source = $_FILES["zip_file"]["tmp_name"];
	$type = $_FILES["zip_file"]["type"];
	
	$name = explode(".", $filename);
	$accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
	foreach($accepted_types as $mime_type) {
		if($mime_type == $type) {
			$okay = true;
			break;
		} 
	}
	
	$continue = strtolower($name[1]) == 'zip' ? true : false;
	if(!$continue) {
		$message = "The file you are trying to upload is not a .zip file. Please try again.";
	}

	$target_path = "c:\\xampp\\htdocs\\drip\\upload\\".$filename;  // change this to the correct site path
	if(move_uploaded_file($source, $target_path)) {
		$zip = new ZipArchive();
		$x = $zip->open($target_path);
		if ($x === true) {
			$oldname = $zip->getNameIndex(0);
			$zip->extractTo("c:\\xampp\\htdocs\\drip\\upload\\"); // change this to the correct site path
			$zip->close();
			rename("c:\\xampp\\htdocs\\drip\\upload\\".$oldname,"c:\\xampp\\htdocs\\drip\\xdrip.sqlite");
			unlink($target_path);
		}
		$message = "<a href='drip/dbloadfromsqlite.php'>Click Here</a> to import new data.";
	} else {	
		$message = "There was a problem with the upload. Please try again.";
	}
}
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Xdrip Zip Upload</title>
</head>

<body>
<?php if(isset($message)) echo "<p>$message</p>"; 
else {?>
<form enctype="multipart/form-data" method="post" action="">
<label>Choose a zip file to upload: <input type="file" name="zip_file" /></label>
<br />
<input type="submit" name="submit" value="Upload" />
</form>
<?php
}?>
</body>
</html>
