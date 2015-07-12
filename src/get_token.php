<?php

$curl = curl_init("http://api.perk.com/oauth/token");

$file = file_get_contents("config.json");
$data = json_decode($file);

$fields = array(
        "grant_type" => "password",
        "username" => $data->username,
        "password" => $data->password,
        "type" => "email",
        "device_type" => "web_browser",
        "client_id" => "11111",
        "client_secret" => "c437a24bf277dfea375f"
    );

foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string, '&');

curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $fields_string
    )
);

$result = curl_exec($curl);

$json_result = json_decode($result);

echo $json_result->access_token;

$file = file_get_contents("config.json");
$data = json_decode($file);
$data->token = $json_result->access_token;
$new_data = json_encode($data,JSON_PRETTY_PRINT);
file_put_contents("config.json", $new_data)

?>