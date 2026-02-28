<?php
require_once("config/db.php");

$slug=$_GET['slug']??'';

$stmt=$conn->prepare("SELECT officename,pincode FROM post_offices WHERE slug=? LIMIT 1");
$stmt->bind_param("s",$slug);
$stmt->execute();
$res=$stmt->get_result();

if($res->num_rows==0){
    header("Location:/404.php");
    exit;
}

$d=$res->fetch_assoc();
$officeSlug=strtolower(preg_replace('/[^a-z0-9]+/','-',trim($d['officename'])));
$officeSlug=trim($officeSlug,'-');
$canonicalPath="/{$officeSlug}-post-office-{$d['pincode']}";

header("Location: {$canonicalPath}", true, 301);
exit;
