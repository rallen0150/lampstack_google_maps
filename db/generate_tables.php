<?php
include_once("connection.php");

// Setting up some basic tables that will need to be run first to create the tables in the maps database on your local db
$createusertable = 'CREATE TABLE IF NOT EXISTS users (
    user_id int AUTO_INCREMENT,
    PRIMARY KEY (user_id),
    username varchar(50) UNIQUE,
    password text,
    firstname varchar(100),
    lastname varchar(100)
);';

if(!$result = $con->query($createusertable)){
    die('There was an error running the query 1 [' . $con->error . ']');
}

$createmappointtable = 'CREATE TABLE IF NOT EXISTS point_of_interests (
    poi_id int AUTO_INCREMENT,
    PRIMARY KEY (poi_id),
    user_id int, 
    point_lat decimal (5,3),
    point_long decimal (6,3),
    point_text text,
    address text
);';
   
if(!$result = $con->query($createmappointtable)){
    die('There was an error running the query 2 [' . $con->error . ']');
}