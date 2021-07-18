<?php
session_start();
include_once("db/connection.php");

$username = $_POST['username'] ?? "";
$password = $_POST['password'] ?? "";

if (isset($_POST['action']) && $_POST['action'] == "login") {
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password' ";
    if(!$result = $con->query($query)){
        die('There was an error running the query [' . $con->error . ']');
    } else {
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // store the user_id as a session variable so there is no need to put the user_id in the url to try to access someone else's list
            $_SESSION['user_id'] = $row['user_id'];
            // redirect to map page after successful login
            echo "<script>window.location.href = '/map.php';</script>";
        }
        // Alert shows up that the username and password didn't match
        echo "<script>alert('Incorrect username or password');</script>";
    }  
}

// unset the session variable to allow another login if the logout button was clicked
if (isset($_GET['logout'])) unset($_SESSION['user_id']);
// redirect to the maps page if a user is already logged in
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != "") echo "<script>window.location.href = '/map.php';</script>"; 
?>
<!DOCTYPE html>
<html>    
    <head>    
        <title>Login Form</title>    
        <link rel="stylesheet" type="text/css" href="css/style.css">    
    </head>    
    <body>    
    <div class="login">
        <h1>Login to Web App</h1>
        <form method="post" action="index.php">
            <p><input type="text" name="username" id="username" value="" placeholder="Username"></p>
            <p><input type="password" name="password" id="password" value="" placeholder="Password"></p>
            <p class="submit"><input type="submit" class="login_btn" name="commit" value="Login"></p>
            <input type="hidden" name="action" value="login">
            <div class="new_user">
                <p><a href="new_user.php">Create New User</a></p>
            </div>  
        </form>
    </div>
    </body>    
</html>     
