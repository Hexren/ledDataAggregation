<?php
/**
 * This file is an example on how to use Synology_Api
 */

set_include_path(dirname(__FILE__).'/synology/library'.PATH_SEPARATOR.get_include_path());
function __autoload($class_name) {
	$path = str_replace('_', DIRECTORY_SEPARATOR, $class_name);

	include $path . '.php';

}

//get data from synology
$synology = new Synology_FileStation_Api('192.168.1.83', 5000, 'http', 1);
$synology->connect('admin', 'sdlkjfhljkdfh435834598345fhjhgjhdgfcvb4564sasopo');
$shares = $synology->getShares(false, 1, 0, 'name', 'asc', True);

foreach($shares->shares as $share){  
    $freespace = $share->additional->volume_status->freespace/$share->additional->volume_status->totalspace;
}

$synology->disconnect();

//push data to activeMq
/*
$con = new Stomp("tcp://192.168.1.100:61613");
$con->connect();
$con->send("/queue/diskSpaceOli", $freespace);
*/

// create curl resource
$ch = curl_init();

// push data to atrium led
curl_setopt($ch, CURLOPT_URL, "http://192.168.1.61/data/put/diskOli/".$freespace);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);      
