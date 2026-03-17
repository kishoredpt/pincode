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

$pincodeOffices = [];
$nearbyPincodes = [];
$primaryPincodeRow = null;

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

    $stmt = $conn->prepare("SELECT * FROM post_offices WHERE pincode=? ORDER BY officename");
    $stmt->bind_param('s', $pincode);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($record = $result->fetch_assoc()) {
        $pincodeOffices[] = $record;
    }

    $primaryPincodeRow = $pincodeOffices[0] ?? null;
    if (!$primaryPincodeRow) {
        header("Location:/404.php");
        exit;
    }

    $pageTitle = "Pincode " . $pincode . " Post Office Details - " . $primaryPincodeRow['district'];
    $metaDescription = "Find all post offices under pincode " . $pincode . " in " . $primaryPincodeRow['district'] . " district of " . $primaryPincodeRow['statename'] . ".";

    $stmt = $conn->prepare(
        "SELECT pincode, district, statename, COUNT(*) AS office_count
         FROM post_offices
         WHERE district = ? AND pincode <> ?
         GROUP BY pincode, district, statename
         ORDER BY ABS(CAST(pincode AS UNSIGNED) - CAST(? AS UNSIGNED)) ASC
         LIMIT 8"
    );
    $stmt->bind_param('sss', $primaryPincodeRow['district'], $pincode, $pincode);
    $stmt->execute();
    $nearbyResult = $stmt->get_result();

    while ($nearby = $nearbyResult->fetch_assoc()) {
        $nearbyPincodes[] = $nearby;
    }
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
<div class="grid md:grid-cols-2 gap-4">
<?php foreach ($pincodeOffices as $officeRow): ?>
<div class="card">
<b><?= htmlspecialchars($officeRow['officename']); ?></b><br>
<?= htmlspecialchars($officeRow['district']); ?>, <?= htmlspecialchars($officeRow['statename']); ?><br>
Delivery: <?= htmlspecialchars($officeRow['delivery']); ?>
</div>
<?php endforeach; ?>
</div>

<?php
$officeCount = count($pincodeOffices);
$officeNames = array_values(array_filter(array_map(static fn($item) => trim((string)($item['officename'] ?? '')), $pincodeOffices)));
$deliveryTypes = array_values(array_unique(array_filter(array_map(static fn($item) => trim((string)($item['delivery'] ?? '')), $pincodeOffices))));
$deliverySummary = !empty($deliveryTypes) ? implode(', ', $deliveryTypes) : 'Delivery information varies by post office.';
$sampleOffices = implode(', ', array_slice($officeNames, 0, 5));
$wordingVariant = ((int)$pincode) % 3;
?>

<section class="card mt-8">
    <h2>About this post office network</h2>
    <p>
        PIN code <b><?= htmlspecialchars($pincode); ?></b> serves a practical postal cluster in <?= htmlspecialchars($primaryPincodeRow['district']); ?> district, <?= htmlspecialchars($primaryPincodeRow['statename']); ?>.
        Within this code, India Post currently maps <b><?= htmlspecialchars((string)$officeCount); ?></b> listed office<?= $officeCount === 1 ? '' : 's'; ?>, helping residents, shops, institutions, and local service providers route letters and parcels correctly.
        The better your address format, the faster mail moves from sorting center to doorstep.
    </p>
    <p>
        Commonly referenced offices under this PIN include <?= htmlspecialchars($sampleOffices !== '' ? $sampleOffices : 'multiple branch and sub offices'); ?>.
        Delivery classification for this cluster appears as <b><?= htmlspecialchars($deliverySummary); ?></b>, which usually indicates whether the address receives direct delivery or depends on pickup/distribution through a nearby head office.
        If you are filling out e-commerce, banking, KYC, insurance, or educational forms, use the exact office locality along with this PIN to avoid misrouting.
    </p>
    <p>
        <?php if ($wordingVariant === 0): ?>
            This page is tailored for quick verification, but it is also useful for planning logistics, estimating courier serviceability, and validating office jurisdiction before sending time-sensitive documents.
        <?php elseif ($wordingVariant === 1): ?>
            Besides regular letter delivery, this code is frequently used for identity documents, marketplace shipments, and address verification checks where even one wrong digit can delay processing.
        <?php else: ?>
            People often check this code before dispatching legal papers, medicine parcels, and value shipments; using the correct office + PIN combination reduces return-to-sender cases significantly.
        <?php endif; ?>
    </p>
</section>

<section class="card mt-6">
    <h2>How this pincode is used</h2>
    <p>
        A 6-digit PIN is not only a location tag; it is part of the operational workflow of India Post and private couriers.
        For <?= htmlspecialchars($pincode); ?>, the first digit indicates the broader postal zone, the next digits narrow the regional sorting route, and the final set identifies the destination delivery segment.
        When you enter this PIN correctly, systems can auto-assign tax region checks, delivery timelines, and last-mile hubs more accurately.
    </p>
    <p>
        In day-to-day use, residents apply this code while ordering online, requesting official certificates, opening financial accounts, registering utility connections, and receiving government communication.
        Businesses use the same PIN for GST invoices, reverse logistics pickups, and supplier dispatch planning.
        In short, this single code helps both public and private networks confirm that the address belongs to the right locality in <?= htmlspecialchars($primaryPincodeRow['district']); ?>.
    </p>
    <p>
        For best results, always pair the PIN with house number, street/locality, post office name, district, and state.
        That complete format reduces failed delivery attempts and ensures support teams can quickly locate your address if there is a shipment exception.
    </p>
</section>

<?php if (!empty($nearbyPincodes)): ?>
<section class="card mt-6">
    <h2>Nearby areas (if available)</h2>
    <p>
        The following nearby PIN code areas are commonly searched together with <?= htmlspecialchars($pincode); ?> in <?= htmlspecialchars($primaryPincodeRow['district']); ?> district.
        Use these links if you are checking neighboring delivery zones or comparing office coverage:
    </p>
    <ul>
        <?php foreach ($nearbyPincodes as $nearby): ?>
            <li>
                <a class="text-blue-600 underline" href="/<?= urlencode($nearby['pincode']); ?>-pincode">
                    <?= htmlspecialchars($nearby['pincode']); ?>
                </a>
                - <?= htmlspecialchars($nearby['district']); ?>, <?= htmlspecialchars($nearby['statename']); ?>
                (<?= htmlspecialchars((string)$nearby['office_count']); ?> office<?= ((int)$nearby['office_count'] === 1) ? '' : 's'; ?>)
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>

<section class="card mt-6">
    <h2>FAQs</h2>

    <h3>1) What is the correct way to write this PIN code in an address?</h3>
    <p>
        Write the full 6-digit PIN (<?= htmlspecialchars($pincode); ?>) on the last line with the post office/locality name, followed by <?= htmlspecialchars($primaryPincodeRow['district']); ?>, <?= htmlspecialchars($primaryPincodeRow['statename']); ?>.
        Do not shorten digits or replace the code with a nearby one, even if the locations are close.
    </p>

    <h3>2) Can one PIN code have multiple post offices?</h3>
    <p>
        Yes. This PIN currently maps <?= htmlspecialchars((string)$officeCount); ?> office<?= $officeCount === 1 ? '' : 's'; ?>.
        Multiple branch or sub offices often share the same code when they are served through related sorting and delivery channels.
    </p>

    <h3>3) Why does delivery sometimes fail even when the PIN is correct?</h3>
    <p>
        The PIN alone is not enough in dense areas.
        Missing house number, wrong locality spelling, or incomplete landmark details can still cause delays.
        Always include full address details along with contact number for courier verification.
    </p>

    <h3>4) Is this PIN code valid for speed post and e-commerce delivery checks?</h3>
    <p>
        In most cases, yes.
        Platforms use PIN-based serviceability first, then office-level rules for final confirmation.
        If a shipment is urgent, verify both the PIN and specific post office name before dispatch.
    </p>
</section>
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
