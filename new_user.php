<?php
include_once("db/connection.php");

$username = $_POST['username'] ?? "";
$password = $_POST['password'] ?? ""; 
$firstname = $_POST['firstname'] ?? "";
$lastname = $_POST['lastname'] ?? "";

if (isset($_POST['action']) && $_POST['action'] == "create_user") {
    $query = "INSERT INTO users (username, password, firstname, lastname) VALUES ('$username', '$password', '$firstname', '$lastname') ";
    if(!$result = $con->query($query)){
        die('There was an error running the query [' . $con->error . ']');
    } else {
        // redirect to login page so the user can officially login
        echo "<script>window.location.href = 'index.php';</script>";
    }  
}
?>
<!DOCTYPE html>
<html>    
    <head>    
        <title>New User</title>    
        <link rel="stylesheet" type="text/css" href="css/style.css">    
    </head>    
    <body>    
    <div class="create_user_div">
        <h1>Create New User</h1>
        <form method="post" action="new_user.php">
            <p><input type="text" name="username" id="username" value="" placeholder="Username"></p>
            <p><input type="password" name="password" id="password" value="" placeholder="Password"></p>
            <p><input type="text" name="firstname" id="firstname" value="" placeholder="Firstname"></p>
            <p><input type="text" name="lastname" id="lastname" value="" placeholder="Lastname"></p>
            <p class="submit"><input type="submit" class="createuser_btn" name="commit" value="Create User"></p>
            <input type="hidden" name="action" value="create_user">
        </form>
    </div>
    </body>    
</html>     
