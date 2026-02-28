<?php
require_once 'config/db.php';

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    header("Location:/404.php");
    exit;
}

/* -----------------------------
   Detect Page Type
------------------------------*/
if (str_contains($slug, 'post-office')) {
    $type = "office";
} elseif (str_contains($slug, 'pincode')) {
    $type = "pincode";
} elseif (str_contains($slug, 'district')) {
    $type = "district";
} else {
    header("Location:/404.php");
    exit;
}

if ($type === "office") {
    preg_match('/pincode-(\d+)/', $slug, $match);
    $pincode = $match[1] ?? '';

    if (!preg_match('/^\d{6}$/', $pincode)) {
        header("Location:/404.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM post_offices WHERE pincode=? LIMIT 1");
    $stmt->bind_param('s', $pincode);
    $stmt->execute();
    $result = $stmt->get_result();
    $office = $result->fetch_assoc();

    if (!$office) {
        header("Location:/404.php");
        exit;
    }

    $pageTitle = $office['officename'] . " Post Office - " . $office['pincode'];
    $metaDescription = "Complete details of " . $office['officename'] . " Post Office located in " . $office['district'] . ", " . $office['statename'] . " including delivery status and location.";
}

if ($type === "pincode") {
    preg_match('/(\d{6})/', $slug, $match);
    $pincode = $match[1] ?? '';

    if (!preg_match('/^\d{6}$/', $pincode)) {
        header("Location:/404.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM post_offices WHERE pincode=?");
    $stmt->bind_param('s', $pincode);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();
    if (!$row) {
        header("Location:/404.php");
        exit;
    }

    $pageTitle = "Pincode " . $pincode . " Post Office Details - " . $row['district'];
    $metaDescription = "Find all post offices under pincode " . $pincode . " in " . $row['district'] . " district of " . $row['statename'] . ".";
}

if ($type === "district") {
    $district = strtoupper(str_replace("-", " ", explode("-district", $slug)[0] ?? ''));
    if ($district === '') {
        header("Location:/404.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM post_offices WHERE district=? LIMIT 50");
    $stmt->bind_param('s', $district);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();
    if (!$row) {
        header("Location:/404.php");
        exit;
    }

    $pageTitle = $district . " District Post Offices List";
    $metaDescription = "Complete list of post offices located in " . $district . " district with pincodes and delivery information.";
}

include 'includes/header.php';
?>

<div class="container" style="padding:40px 0;">
<h1><?= htmlspecialchars($pageTitle); ?></h1>
<p class="mb-8 text-gray-700"><?= htmlspecialchars($metaDescription); ?></p>

<?php if ($type === "office"): ?>
<div class="card">
<p><b>Office Name:</b> <?= htmlspecialchars($office['officename']); ?></p>
<p><b>Pincode:</b> <?= htmlspecialchars($office['pincode']); ?></p>
<p><b>District:</b> <?= htmlspecialchars($office['district']); ?></p>
<p><b>State:</b> <?= htmlspecialchars($office['statename']); ?></p>
<p><b>Delivery:</b> <?= htmlspecialchars($office['delivery']); ?></p>

<?php if (!empty($office['latitude'])): ?>
<a target="_blank" rel="noopener"
href="https://www.google.com/maps?q=<?= urlencode($office['latitude']); ?>,<?= urlencode($office['longitude']); ?>"
class="text-blue-600 underline mt-3 inline-block">View Location on Google Maps</a>
<?php endif; ?>
</div>
<?php endif; ?>

<?php if ($type === "pincode"): ?>
<?php
$stmt = $conn->prepare("SELECT * FROM post_offices WHERE pincode=?");
$stmt->bind_param('s', $pincode);
$stmt->execute();
$result = $stmt->get_result();
?>
<div class="grid md:grid-cols-2 gap-4">
<?php while ($row = $result->fetch_assoc()): ?>
<div class="card">
<b><?= htmlspecialchars($row['officename']); ?></b><br>
<?= htmlspecialchars($row['district']); ?>, <?= htmlspecialchars($row['statename']); ?><br>
Delivery: <?= htmlspecialchars($row['delivery']); ?>
</div>
<?php endwhile; ?>
</div>
<?php endif; ?>

<?php if ($type === "district"): ?>
<?php
$stmt = $conn->prepare("SELECT * FROM post_offices WHERE district=? LIMIT 100");
$stmt->bind_param('s', $district);
$stmt->execute();
$result = $stmt->get_result();
?>
<div class="grid md:grid-cols-2 gap-4">
<?php while ($row = $result->fetch_assoc()): ?>
<div class="card">
<?= htmlspecialchars($row['officename']); ?><br>
Pincode: <?= htmlspecialchars($row['pincode']); ?>
</div>
<?php endwhile; ?>
</div>
<?php endif; ?>
</div>

<?php if ($type === "office"): ?>
<script type="application/ld+json">
{
 "@context":"https://schema.org",
 "@type":"FAQPage",
 "mainEntity":[
 {
   "@type":"Question",
   "name":"What is the pincode of <?= addslashes($office['officename']); ?>?",
   "acceptedAnswer":{
     "@type":"Answer",
     "text":"The pincode of <?= addslashes($office['officename']); ?> is <?= addslashes($office['pincode']); ?>."
   }
 },
 {
   "@type":"Question",
   "name":"Where is <?= addslashes($office['officename']); ?> located?",
   "acceptedAnswer":{
     "@type":"Answer",
     "text":"<?= addslashes($office['officename']); ?> Post Office is located in <?= addslashes($office['district']); ?> district of <?= addslashes($office['statename']); ?>, India."
   }
 }
 ]
}
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
