<?php
session_start();
include_once("connection.php");

$latitude = isset($_GET['lat']) ? $_GET['lat'] : $_POST['lat'];
$longitude = isset($_GET['long']) ? $_GET['long'] : $_POST['long'];
$address = isset($_GET['address']) ? $_GET['address'] : $_POST['address'];
$text = isset($_GET['name']) ? $_GET['name'] : $_POST['name'];

if ($address != "") {
    $query = "INSERT INTO point_of_interests (user_id, point_lat, point_long, point_text, address) VALUES ('".$_SESSION['user_id']."', '$latitude', '$longitude', '$text', '$address') ";
    if(!$result = $con->query($query)){
        die('There was an error running the query [' . $con->error . ']');
    } else {
        echo json_encode("Added");
    }
}