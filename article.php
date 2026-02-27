<?php
include "config/db.php";

$slug = $_GET['slug'] ?? '';

/* -----------------------------
   Detect Page Type
------------------------------*/

if(str_contains($slug,'post-office')){
   $type="office";
}
elseif(str_contains($slug,'pincode')){
   $type="pincode";
}
elseif(str_contains($slug,'district')){
   $type="district";
}
else{
   die("Invalid page");
}


/* -----------------------------
   OFFICE PAGE
------------------------------*/

if($type=="office"){

preg_match('/pincode-(\d+)/',$slug,$match);
$pincode = $match[1];

$sql = "SELECT * FROM post_offices WHERE pincode='$pincode' LIMIT 1";
$result = $conn->query($sql);
$office = $result->fetch_assoc();

$title = $office['officename']." Post Office - ".$office['pincode'];
$description = "Complete details of ".$office['officename']." Post Office located in ".$office['district'].", ".$office['statename']." including delivery status and location.";

}

/* -----------------------------
   PINCODE PAGE
------------------------------*/

if($type=="pincode"){

preg_match('/(\d{6})/',$slug,$match);
$pincode = $match[1];

$sql = "SELECT * FROM post_offices WHERE pincode='$pincode'";
$result = $conn->query($sql);

$row = $result->fetch_assoc();

$title = "Pincode ".$pincode." Post Office Details - ".$row['district'];
$description = "Find all post offices under pincode ".$pincode." in ".$row['district']." district of ".$row['statename'].".";

}

/* -----------------------------
   DISTRICT PAGE
------------------------------*/

if($type=="district"){

$district = strtoupper(str_replace("-"," ",explode("-district",$slug)[0]));

$sql="SELECT * FROM post_offices WHERE district='$district' LIMIT 50";
$result=$conn->query($sql);

$row = $result->fetch_assoc();

$title = $district." District Post Offices List";
$description = "Complete list of post offices located in ".$district." district with pincodes and delivery information.";

}

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">

<title><?php echo $title; ?></title>

<meta name="description" content="<?php echo $description; ?>">

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-white text-black">

<div class="max-w-4xl mx-auto px-4 py-10">

<h1 class="text-3xl font-bold mb-6">
<?php echo $title; ?>
</h1>

<p class="mb-8 text-gray-700">
<?php echo $description; ?>
</p>

<?php
/* -----------------------------
   OFFICE OUTPUT
------------------------------*/
if($type=="office"){
?>

<div class="border rounded-xl p-6 shadow">

<p><b>Office Name:</b> <?php echo $office['officename']; ?></p>
<p><b>Pincode:</b> <?php echo $office['pincode']; ?></p>
<p><b>District:</b> <?php echo $office['district']; ?></p>
<p><b>State:</b> <?php echo $office['statename']; ?></p>
<p><b>Delivery:</b> <?php echo $office['delivery']; ?></p>

<?php if($office['latitude']){ ?>
<a target="_blank"
href="https://www.google.com/maps?q=<?php echo $office['latitude']; ?>,<?php echo $office['longitude']; ?>"
class="text-blue-600 underline mt-3 inline-block">
View Location on Google Maps
</a>
<?php } ?>

</div>

<?php } ?>


<?php
/* -----------------------------
   PINCODE OUTPUT
------------------------------*/
if($type=="pincode"){

$result = $conn->query("SELECT * FROM post_offices WHERE pincode='$pincode'");
?>

<div class="grid md:grid-cols-2 gap-4">

<?php while($row=$result->fetch_assoc()){ ?>

<div class="border rounded-lg p-4 shadow">
<b><?php echo $row['officename']; ?></b><br>
<?php echo $row['district']; ?>, <?php echo $row['statename']; ?><br>
Delivery: <?php echo $row['delivery']; ?>
</div>

<?php } ?>

</div>

<?php } ?>


<?php
/* -----------------------------
   DISTRICT OUTPUT
------------------------------*/
if($type=="district"){
?>

<div class="grid md:grid-cols-2 gap-4">

<?php
$result=$conn->query("SELECT * FROM post_offices WHERE district='$district' LIMIT 100");

while($row=$result->fetch_assoc()){
?>

<div class="border p-4 rounded shadow">
<?php echo $row['officename']; ?><br>
Pincode: <?php echo $row['pincode']; ?>
</div>

<?php } ?>

</div>

<?php } ?>

</div>


<!-- =============================
STEP 8 — FAQ SCHEMA (SEO BOOST)
============================= -->

<?php if($type=="office"){ ?>

<script type="application/ld+json">
{
 "@context":"https://schema.org",
 "@type":"FAQPage",
 "mainEntity":[
 {
   "@type":"Question",
   "name":"What is the pincode of <?php echo $office['officename']; ?>?",
   "acceptedAnswer":{
     "@type":"Answer",
     "text":"The pincode of <?php echo $office['officename']; ?> is <?php echo $office['pincode']; ?>."
   }
 },
 {
   "@type":"Question",
   "name":"Where is <?php echo $office['officename']; ?> located?",
   "acceptedAnswer":{
     "@type":"Answer",
     "text":"<?php echo $office['officename']; ?> Post Office is located in <?php echo $office['district']; ?> district of <?php echo $office['statename']; ?>, India."
   }
 }
 ]
}
</script>

<?php } ?>

</body>
</html>