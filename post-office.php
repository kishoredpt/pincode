<?php
require_once "config/db.php";
include "includes/header.php";

$pincode=$pageData[0]['pincode'] ?? '';
$district=$pageData[0]['district'] ?? '';
$state=$pageData[0]['statename'] ?? '';
?>

<div class="container">

<h1>Pincode <?php echo $pincode; ?></h1>

<?php foreach($pageData as $office){ ?>

<div class="po-card">
<h3><?php echo $office['officename']; ?></h3>
<p>
<?php echo $office['district']; ?>,
<?php echo $office['statename']; ?>
</p>
</div>

<?php } ?>

<hr>

<div class="seo-content">

<h2>About Pincode <?php echo $pincode; ?></h2>

<p>
PIN Code <?php echo $pincode; ?> belongs to
<?php echo $district; ?> district in
<?php echo $state; ?> state of India.
This postal region includes multiple delivery
post offices managed under India Post services.
</p>

<p>
PIN codes help streamline parcel delivery,
government communication, banking verification,
and logistics operations throughout India.
</p>

</div>

</div>

<?php include "includes/footer.php"; ?>
