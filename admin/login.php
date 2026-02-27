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
<form method="POST">
<input name="username" required>
<input type="password" name="password" required>
<button>Login</button>
</form>