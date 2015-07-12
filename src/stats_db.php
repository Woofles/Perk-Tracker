<?php
// Content of database.php
 
if (!function_exists('mysqli_init') && !extension_loaded('mysqli')) {
    echo 'Your web host sucks.';
    die();
}
 
$file = file_get_contents("config.json");
$data = json_decode($file);

$mysqli = new mysqli($data->db_host, $data->db_username, $data->db_password, $data->db_name);
 
if($mysqli->connect_errno) {
    printf("Connection Failed: %s\n", $mysqli->connect_error);
    exit;
}

?>