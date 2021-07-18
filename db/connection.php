<?php
// Open a mysql connection to your host under the 'maps' database
$con = new mysqli("localhost", "root", "root", "maps");
 
if($con->connect_errno > 0){
    die('Unable to connect to database! [' . $con->connect_error . ']');
}