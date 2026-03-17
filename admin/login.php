<?php
session_start();
include("../config/db.php");

if($_POST){
$stmt=$conn->prepare("SELECT * FROM admin_users WHERE username=?");
$stmt->bind_param("s",$_POST['username']);
$stmt->execute();
$res=$stmt->get_result();
$user=$res->fetch_assoc();
if($user && password_verify($_POST['password'],$user['password'])){
$_SESSION['admin']=true;
header("Location:dashboard.php"); exit;
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login | India Pincode Locator</title>
<meta name="description" content="Secure admin login for India Pincode Locator editorial and operations dashboard access.">
</head>
<body>
<h1>Admin Login</h1>
<form method="POST">
<input name="username" required>
<input type="password" name="password" required>
<button>Login</button>
</form>
</body>
</html>
