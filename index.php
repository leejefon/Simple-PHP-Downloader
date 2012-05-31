<?php
/**
 * Index
 *
 * Call to the downloader or displays the downloads if now routes defined
 *
 * Written By: Jeff Lee
 * Created On: 2012/05
 */

// GET options for zip

$uri = str_replace("/download",  "", $_SERVER["REDIRECT_URL"]);

require_once("Downloader.php");

if (!empty($uri)) { // no request
	$dl = new Downloader();
	$dl->download(explode("/", $uri));

} else {
	echo "hi";
	// print table
}

?>
