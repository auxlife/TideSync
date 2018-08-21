# TideSync
Script to sync Xdrip to Tidepool

This script is two in one
v1 API - acts as an ns api endpoint for xdrip to upload to in real time
upload - can be used to bulk upload of data from xdrip to tidepool


v1 API:
in xdrip you would goto settings> cloud upload> nightscount sync > base url and add your tidesync url as http(s)://fakekey@(yoursite)/(tidesync)/api/v1/ (if you already have a looping rig and or ns website address you can just add space behind that entry and add your tidesync url).

Upload:
If you want your upload directory to be password protected:
on windows: htpasswd.exe -c -b .htpasswd {your username} {your password}
edit /upload/.htaccess with the path of htpasswd file

Url to upload zip file from your phone: {your site}/{folder with tidesync}/upload/index.php
in xdrip goto the the dots on the upper right hand side and goto import/export features > export database; it will go back to xdrip main window and should say it completed the export and where it saved the file in a few seconds. now open your browser and goto thw upload url, enter your username/pass, and select the zip file on your phone and upload, once the file has been uploaded, click the click here link to import any new reading to the mysql db and begin the batch upload process to tidepool.


