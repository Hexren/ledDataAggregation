<?php

#call as
#getFreeSpace.php <ip> <port> <user> <pass>
#e.g.
#getFreeSpace.php 127.0.0.1 5000 admin test123


set_include_path(dirname(__FILE__).'/synology/library'.PATH_SEPARATOR.get_include_path());
function __autoload($class_name) {
	$path = str_replace('_', DIRECTORY_SEPARATOR, $class_name);

	include $path . '.php';

}

//args
$ip = $argv[1];
$port = $argv[2];
$user = $argv[3];
$pass = $argv[4];
$name = $argv[5];

//get data from synology
$synology = new Synology_FileStation_Api($ip, $port, 'http', 1);
$synology->connect($user, $pass);
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
curl_setopt($ch, CURLOPT_URL, "http://192.168.1.61/data/put/".$name."/".$freespace);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);      
