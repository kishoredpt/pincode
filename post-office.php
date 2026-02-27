<?php
include("config/db.php");
require_once("includes/breadcrumb.php");

$slug=$_GET['slug']??'';

$stmt=$conn->prepare("SELECT * FROM post_offices WHERE slug=? LIMIT 1");
$stmt->bind_param("s",$slug);
$stmt->execute();
$res=$stmt->get_result();

if($res->num_rows==0){
    header("Location:/404.php");
    exit;
}

$d=$res->fetch_assoc();

/* SEO */
$page_title=$d['officename']." Post Office - ".$d['district']." ".$d['statename']." Pincode ".$d['pincode'];

$meta_description=
"Complete information about ".$d['officename']." Post Office in ".
$d['district'].", ".$d['statename'].
". View pincode ".$d['pincode'].
", delivery status and postal details.";

include("includes/header.php");
?>

<div class="container">

<?php
breadcrumb_schema([
"Home"=>"https://".$_SERVER['HTTP_HOST']."/",
$d['statename']=>"",
$d['district']=>"",
$d['officename']=>""
]);
?>

<h1><?= htmlspecialchars($d['officename']); ?></h1>

<div class="card">

<p><strong>Pincode:</strong> <?= $d['pincode']; ?></p>
<p><strong>District:</strong> <?= $d['district']; ?></p>
<p><strong>State:</strong> <?= $d['statename']; ?></p>
<p><strong>Office Type:</strong> <?= $d['officetype']; ?></p>
<p><strong>Delivery Status:</strong> <?= $d['delivery']; ?></p>

</div>

</div>

<!-- POST OFFICE STRUCTURED DATA -->
<script type="application/ld+json">
{
"@context":"https://schema.org",
"@type":"PostOffice",
"name":"<?= addslashes($d['officename']); ?>",
"address":{
 "@type":"PostalAddress",
 "addressLocality":"<?= $d['district']; ?>",
 "addressRegion":"<?= $d['statename']; ?>",
 "postalCode":"<?= $d['pincode']; ?>",
 "addressCountry":"IN"
},
"areaServed":"<?= $d['district']; ?>"
}
</script>

<?php include("includes/footer.php"); ?>