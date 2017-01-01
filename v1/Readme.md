the .htacess file is needed as Xdrip requires the end of the rest api to be v1 or we won't get all data uploaded.
the post request xdrip is sent to URL/v1/entries; so when the post is made to /v1/entries the htacess will direct the post to entries.php
