<?php
header("Content-Type: application/json");
require_once "config/db.php";

$type=$_GET['type'] ?? '';

/* STATES */
if($type=="states"){

$stmt=$conn->prepare("
SELECT DISTINCT statename
FROM post_offices
ORDER BY statename
");

$stmt->execute();
$result=$stmt->get_result();

$data=[];
while($row=$result->fetch_assoc()){
$data[]=$row['statename'];
}

echo json_encode($data);
exit;
}

/* DISTRICTS */
if($type=="districts"){

$state=$_GET['state'];

$stmt=$conn->prepare("
SELECT DISTINCT district
FROM post_offices
WHERE statename=?
ORDER BY district
");

$stmt->bind_param("s",$state);
$stmt->execute();

$result=$stmt->get_result();

$data=[];
while($row=$result->fetch_assoc()){
$data[]=$row['district'];
}

echo json_encode($data);
exit;
}

/* OFFICES */
if($type=="offices"){

$state=$_GET['state'];
$district=$_GET['district'];

$stmt=$conn->prepare("
SELECT DISTINCT officename
FROM post_offices
WHERE statename=? AND district=?
ORDER BY officename
");

$stmt->bind_param("ss",$state,$district);
$stmt->execute();

$result=$stmt->get_result();

$data=[];
while($row=$result->fetch_assoc()){
$data[]=$row['officename'];
}

echo json_encode($data);
exit;
}

/* SEARCH */
if($type=="search"){

$state=$_GET['state'];
$district=$_GET['district'];
$office=$_GET['office'];

$stmt=$conn->prepare("
SELECT *
FROM post_offices
WHERE statename=? AND district=? AND officename=?
");

$stmt->bind_param("sss",$state,$district,$office);
$stmt->execute();

$result=$stmt->get_result();

$data=[];
while($row=$result->fetch_assoc()){
$data[]=$row;
}

echo json_encode($data);
exit;
}

/* DISTRICT POST OFFICES */
if($type=="district-postoffices"){

$state=$_GET['state'];
$district=$_GET['district'];

$stmt=$conn->prepare("
SELECT officename,pincode,statename,district
FROM post_offices
WHERE statename=? AND district=?
ORDER BY officename
");

$stmt->bind_param("ss",$state,$district);
$stmt->execute();

$result=$stmt->get_result();

$data=[];
while($row=$result->fetch_assoc()){
$data[]=$row;
}

echo json_encode($data);
exit;
}