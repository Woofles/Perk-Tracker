<?php

    require 'stats_db.php';

    $file = file_get_contents("config.json");
    $data = json_decode($file);
    $token = $data->token;

    $stmt = $mysqli->prepare('INSERT INTO perk_stats (current_points, total_points) VALUES (?,?)');

    $id = $data->id;

    $url = 'http://api.perk.com/api/user/id/'.$id.'/token/'.$token.'/';
    $json = file_get_contents_curl($url);
    $object = json_decode($json);
    $availpoints = (int) $object->availableperks;
    $redeempoints = (int) $object->redeemedperks;
    $totalpoints = $availpoints + $redeempoints;
    $stmt->bind_param('dd',$availpoints,$totalpoints);
    if(!$stmt) {
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->execute();
    printf("Inserted new datapoint @ ".date("m/d/Y h:i:sa"));
    $stmt->close();
    
    function file_get_contents_curl($url) {
	$ch = curl_init();
	
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
	curl_setopt($ch, CURLOPT_URL, $url);
	
	$data = curl_exec($ch);
	curl_close($ch);
	
	return $data;
    }

?>