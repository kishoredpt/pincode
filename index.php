<?php
$route = $_GET['route'] ?? '';
$requestPath=parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$requestPath=trim((string)$requestPath,'/');

if($route===''){
    if($requestPath!=='' && !str_contains($requestPath,'/') && !str_contains($requestPath,'.php')){
        $route=$requestPath;
    }
}

if($route==='blog' || $requestPath==='blog'){
    require __DIR__."/blog.php";
    exit;
}

if(preg_match('/^blog\/([a-zA-Z0-9-]+)$/',$requestPath,$blogMatch)){
    $_GET['slug']=$blogMatch[1];
    require __DIR__."/blog-post.php";
    exit;
}

require_once "config/db.php";

$route = $_GET['route'] ?? '';

if($route===''){
    $requestPath=parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $requestPath=trim((string)$requestPath,'/');

    if($requestPath!=='' && !str_contains($requestPath,'/') && !str_contains($requestPath,'.php')){
        $route=$requestPath;
    }
}

$pageType="home";
$pageData=[];

function toSlug($value){
    $value=strtolower(trim($value));
    $value=preg_replace('/[^a-z0-9]+/','-',$value);
    return trim($value,'-');
}

/* =========================
ROUTE ENGINE
========================= */

if($route && preg_match('/^(.+)-post-office-(\d{6})$/',$route,$officeMatch)){

    $officeSlug=$officeMatch[1];
    $pincode=$officeMatch[2];

    $stmt=$conn->prepare("\n        SELECT *\n        FROM post_offices\n        WHERE pincode=?\n        ORDER BY officename\n        LIMIT 100\n    ");
    $stmt->bind_param("s",$pincode);
    $stmt->execute();
    $res=$stmt->get_result();

    $officeMatchRow=null;
    $fallbackRow=null;

    while($row=$res->fetch_assoc()){
        if(!$fallbackRow){
            $fallbackRow=$row;
        }
        if(toSlug($row['officename'])===$officeSlug){
            $officeMatchRow=$row;
            break;
        }
    }

    if($officeMatchRow || $fallbackRow){
        $pageType="office";
        $pageData=$officeMatchRow ?: $fallbackRow;
    }
}
elseif($route){

    $slug=str_ends_with($route,'-pincode')
        ? substr($route,0,-8)
        : $route;
    $name=str_replace('-',' ',$slug);

    /* STATE CHECK */
    $stmt=$conn->prepare("
        SELECT DISTINCT statename
        FROM post_offices
        WHERE LOWER(statename)=LOWER(?)
        LIMIT 1
    ");
    $stmt->bind_param("s",$name);
    $stmt->execute();
    $res=$stmt->get_result();

    if($res->num_rows>0){
        $pageType="state";
        $pageData=$res->fetch_assoc();
    }

    /* DISTRICT CHECK */
    if($pageType=="home"){
        $stmt=$conn->prepare("
            SELECT DISTINCT district
            FROM post_offices
            WHERE LOWER(district)=LOWER(?)
            LIMIT 1
        ");
        $stmt->bind_param("s",$name);
        $stmt->execute();
        $res=$stmt->get_result();

        if($res->num_rows>0){
            $pageType="district";
            $pageData=$res->fetch_assoc();
        }
    }

    /* PINCODE CHECK */
    if(preg_match('/^[0-9]{6}$/',$slug)){

        $stmt=$conn->prepare("
            SELECT *
            FROM post_offices
            WHERE pincode=?
        ");
        $stmt->bind_param("s",$slug);
        $stmt->execute();
        $res=$stmt->get_result();

        if($res->num_rows>0){
            $pageType="pincode";

            while($row=$res->fetch_assoc()){
                $pageData[]=$row;
            }
        }
    }
}
?>

<?php
/* =========================
   SEO META ENGINE (SAFE)
========================= */

/* =========================
   BREADCRUMB ENGINE
========================= */

$breadcrumb = [
    [
        "name" => "Home",
        "url" => "https://pincodelocator.co.in/"
    ]
];

if($route){

    $name = ucwords(str_replace('-',' ',
            str_replace('-pincode','',$route)));

    $breadcrumb[] = [
        "name" => $name,
        "url" => "https://pincodelocator.co.in/".$route
    ];
}

$seoTitle = "India Pincode Search – Find Post Office, District & State";
$seoDescription = "Search Indian PIN Codes, Post Offices, Districts and States across India using official postal data.";

$canonical = "https://pincodelocator.co.in/";
$metaRobots = "index, follow";

/* STATE PAGE */
if($pageType=="office"){

    $seoTitle = $pageData['officename']." Post Office (".$pageData['pincode'].") | "
        .$pageData['district'].", ".$pageData['statename'];
    $seoDescription = "Postal details for ".$pageData['officename']
        ." Post Office, pincode ".$pageData['pincode']
        ." in ".$pageData['district'].", ".$pageData['statename'].".";
    $canonical = "https://pincodelocator.co.in/".$route;
}
elseif($route && in_array($pageType,["state","district","pincode"],true)){

    $name = ucwords(str_replace('-pincode','',$route));
    $name = str_replace('-',' ',$name);

    $seoTitle = "$name Pincode List | Post Offices & District Details";
    $seoDescription = "Complete list of post offices and pincodes in $name state or district. Search locations, delivery offices and postal information.";

    $canonical = "https://pincodelocator.co.in/".$route;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#4f46e5">

<title><?= htmlspecialchars($seoTitle, ENT_QUOTES, "UTF-8") ?></title>

<meta name="description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, "UTF-8") ?>">

<link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, "UTF-8") ?>">

<?php if($route): ?>
<script type="application/ld+json">
{
 "@context": "https://schema.org",
 "@type": "BreadcrumbList",
 "itemListElement": [
<?php foreach($breadcrumb as $i=>$bc): ?>
{
 "@type": "ListItem",
 "position": <?= $i+1 ?>,
 "name": "<?= $bc['name'] ?>",
 "item": "<?= $bc['url'] ?>"
}<?= $i < count($breadcrumb)-1 ? ',' : '' ?>
<?php endforeach; ?>
 ]
}
</script>
<?php endif; ?>

<meta name="robots" content="<?= htmlspecialchars($metaRobots, ENT_QUOTES, "UTF-8") ?>">

<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white text-gray-900">
<style>
  .nav-link { padding: 0.25rem 0.4rem; border-radius: 0.4rem; }
  .nav-link:hover { background: #eef2ff; }
</style>

<div class="max-w-[1100px] mx-auto px-4 md:px-6 py-6 md:py-10">

<!-- TOP BAR -->
<div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-6">
<h1 class="text-2xl md:text-xl font-bold text-center md:text-left">📮 PincodeLocator.co.in</h1>

<nav class="w-full md:w-auto grid grid-cols-3 gap-2 md:flex md:gap-5 text-sm font-medium text-center">
<a class="nav-link" href="/">Home</a>
<a class="nav-link" href="/about.php">About</a>
<a class="nav-link" href="/contact.php">Contact</a>
<a class="nav-link" href="/privacy-policy.php">Privacy</a>
<a class="nav-link" href="/terms.php">Terms</a>
<a class="nav-link" href="/disclaimer.php">Disclaimer</a>
</nav>
</div>

<!-- HEADER -->
<?php
/* ===============================
STATE PAGE
=============================== */
if($pageType=="state"){
?>

<h2 class="text-3xl font-bold mb-8">
<?= strtoupper($pageData['statename']); ?> Pincode List
</h2>

<?php $stateName=trim($pageData['statename']); ?>
<section class="state-intro bg-white rounded-xl shadow p-6 mb-8 leading-7">

<h2 class="text-2xl font-semibold text-indigo-700 mb-4"><?= htmlspecialchars($stateName) ?> PIN Code Directory – Find All Post Offices Easily</h2>

<p class="text-gray-700 mb-4">
<?= htmlspecialchars($stateName) ?> is one of India’s rapidly developing states, known for its growing cities, strong rural networks, and expanding business ecosystem. Whether you are sending official documents, parcels, government applications, or verifying an address for banking or online services, using the correct PIN code is essential for accurate and timely delivery.
</p>

<p class="text-gray-700 mb-4">
The Postal Index Number (PIN) system plays a crucial role in ensuring efficient mail routing across the state. <?= htmlspecialchars($stateName) ?> falls under designated postal zones that help India Post sort and deliver mail systematically. Every district, town, and village in <?= htmlspecialchars($stateName) ?> is assigned a unique 6-digit PIN code that identifies the specific delivery post office responsible for that area.
</p>

<p class="text-gray-700 mb-4">
This page provides a comprehensive directory of all <?= htmlspecialchars($stateName) ?> districts along with access to detailed post office information. Users can explore Head Post Offices, Sub Offices, and Branch Offices across the state. The directory is structured to help residents, businesses, logistics providers, and government users quickly locate reliable postal information without confusion.
</p>

<h3 class="text-xl font-semibold text-indigo-700 mb-3">About <?= htmlspecialchars($stateName) ?> Postal Network</h3>

<p class="text-gray-700 mb-4">
The postal network in <?= htmlspecialchars($stateName) ?> connects major urban centers with semi-urban towns and remote rural regions. From large Head Post Offices managing regional operations to small Branch Offices serving villages, the system supports services such as Speed Post, Registered Post, parcel delivery, and financial services.
</p>

<p class="text-gray-700">
Use the district list below to browse <?= htmlspecialchars($stateName) ?> PIN codes and identify the correct post office for your delivery, documentation, or address verification needs.
</p>

</section>

<?php
$stmt=$conn->prepare("
SELECT district,COUNT(*) total
FROM post_offices
WHERE statename=?
GROUP BY district
ORDER BY district
");
$stmt->bind_param("s",$pageData['statename']);
$stmt->execute();
$res=$stmt->get_result();
?>

<div class="grid md:grid-cols-2 gap-5">

<?php while($row=$res->fetch_assoc()){ ?>
<?php $districtSlug=toSlug($row['district']); ?>
<a class="bg-white p-6 rounded-xl shadow block hover:shadow-md transition"
href="/<?= $districtSlug ?>-pincode">
<h3 class="font-semibold text-2xl mb-2"><?= strtoupper($row['district']); ?></h3>
<p class="text-gray-700 text-lg"><?= $row['total']; ?> Post Offices</p>
</a>
<?php } ?>

</div>

<?php }
elseif($pageType=="district"){
?>

<h2 class="text-3xl font-bold mb-8">
<?= strtoupper($pageData['district']); ?> District Pincode List
</h2>

<?php
$stmt=$conn->prepare("
SELECT officename,pincode,statename,district
FROM post_offices
WHERE district=?
ORDER BY statename,officename
LIMIT 2000
");
$stmt->bind_param("s",$pageData['district']);
$stmt->execute();
$res=$stmt->get_result();
?>

<div class="grid md:grid-cols-2 gap-5">
<?php while($row=$res->fetch_assoc()){
    $officeSlug=toSlug($row['officename']);
?>
<div class="bg-white p-6 rounded-xl shadow">
<h3 class="font-semibold">
<a class="text-indigo-700 hover:underline" href="/<?= $officeSlug ?>-post-office-<?= $row['pincode'] ?>">
<?= htmlspecialchars($row['officename']) ?>
</a>
</h3>
<p><?= htmlspecialchars(strtoupper($row['district'])) ?>, <?= htmlspecialchars(strtoupper($row['statename'])) ?></p>
<p>Pincode: <b><?= htmlspecialchars($row['pincode']) ?></b></p>
</div>
<?php } ?>
</div>

<?php }

/* ===============================
PINCODE PAGE
=============================== */
elseif($pageType=="pincode"){
?>

<?php
$pinCode=trim((string)$pageData[0]['pincode']);
$stateName=trim((string)$pageData[0]['statename']);
?>

<h2 class="text-3xl font-bold mb-8">
Pincode <?= htmlspecialchars($pinCode) ?>
</h2>

<section class="pincode-intro bg-white rounded-xl shadow p-6 mb-8 leading-7">
<p class="text-gray-700 mb-4">
<?= htmlspecialchars($pinCode) ?> is a postal area located in the state of <?= htmlspecialchars($stateName) ?>, India. PIN codes like <?= htmlspecialchars($pinCode) ?> play a critical role in the Indian postal network by uniquely identifying the delivery post office responsible for handling mail and parcels in this region.
</p>

<p class="text-gray-700 mb-4">
This page provides complete information about the post offices associated with PIN code <?= htmlspecialchars($pinCode) ?>, helping residents, businesses, and logistics users find accurate postal details quickly. Whether you need to verify an address, prepare official documents, send parcels through Speed Post or Registered Post, or check serviceability for e-commerce deliveries, this directory gives you clear and structured results.
</p>

<p class="text-gray-700 mb-4">
The Indian Postal Index Number (PIN) system ensures that mail is routed efficiently from national sorting hubs to local delivery offices, reducing errors and speeding up delivery even in rural and semi-urban areas.
</p>

<p class="text-gray-700">
Use the information below to explore the post offices serving the <?= htmlspecialchars($pinCode) ?> region, including key details like district, office type, and locality coverage. Accurate postal data saves time, avoids delivery delays, and ensures your communication and shipments reach their intended destination.
</p>
</section>

<div class="grid md:grid-cols-2 gap-5">

<?php foreach($pageData as $row){ ?>

<div class="bg-white p-6 rounded-xl shadow">

<h3 class="font-semibold">
<?= htmlspecialchars($row['officename']) ?>
</h3>

<p>
<?= htmlspecialchars(strtoupper($row['district'])) ?>,
<?= htmlspecialchars(strtoupper($row['statename'])) ?>
</p>
<p><b>Office Type:</b> <?= htmlspecialchars($row['officetype'] ?? 'N/A') ?></p>
<p><b>Locality:</b> <?= htmlspecialchars($row['taluk'] ?? ($row['divisionname'] ?? 'N/A')) ?></p>

</div>

<?php } ?>

</div>

<?php }

/* ===============================
POST OFFICE PAGE
=============================== */
elseif($pageType=="office"){
?>

<h2 class="text-3xl font-bold mb-8">
<?= htmlspecialchars($pageData['officename']) ?> Post Office - <?= $pageData['pincode'] ?>
</h2>

<div class="bg-white p-6 rounded-xl shadow space-y-2">
<p><b>Office Name:</b> <?= htmlspecialchars($pageData['officename']) ?></p>
<p><b>Pincode:</b> <?= htmlspecialchars($pageData['pincode']) ?></p>
<p><b>District:</b> <?= htmlspecialchars($pageData['district']) ?></p>
<p><b>State:</b> <?= htmlspecialchars($pageData['statename']) ?></p>
<p><b>Office Type:</b> <?= htmlspecialchars($pageData['officetype']) ?></p>
<p><b>Delivery Status:</b> <?= htmlspecialchars($pageData['delivery']) ?></p>
</div>

<?php }
else { ?>
<div class="bg-white rounded-2xl p-6 md:p-10">
<div class="text-center mb-10">

<!-- ===============================
HOMEPAGE AUTHORITY CONTENT
================================== -->
<section class="homepage-content leading-7">
<?php if($route): ?>
<!-- BREADCRUMB -->
<div class="text-sm text-gray-600 mb-6">

<?php foreach($breadcrumb as $index=>$bc): ?>

<a href="<?= $bc['url'] ?>" class="hover:text-indigo-600">
<?= $bc['name'] ?>
</a>

<?php if($index < count($breadcrumb)-1): ?>
<span class="mx-2">›</span>
<?php endif; ?>

<?php endforeach; ?>

</div>
<?php endif; ?>

<h3 class="text-4xl md:text-3xl font-bold text-indigo-700 leading-tight">
India Pincode Locator
</h3>

<p class="mt-3 text-xl md:text-1xl font-medium text-gray-700">
Search 1.6+ Lakh Post Offices Across India
</p>
</div>

<div class="rounded-2xl p-5 md:p-8 border border-gray-200">
<p class="text-gray-700 mb-4">
Welcome to <strong>PincodeLocator.co.in</strong>, your trusted resource for finding accurate Postal Index Numbers (PIN Codes), post office details, and district-level postal information across India. Whether you are verifying an address, preparing official documents, shipping products, or researching postal zones, our platform helps you access structured and easy-to-understand postal data in seconds.
</p>

<p class="text-gray-700 mb-6">
India handles millions of mail transactions every day. Having the correct PIN code ensures that letters, parcels, and important documents reach the right destination without delay. Our directory is designed to simplify postal searches by allowing users to browse state-wise listings, district-level breakdowns, and post office information in a clear and organized format.
</p>

<h3 class="text-2xl font-semibold text-indigo-700 mb-3">How the Indian PIN Code System Works</h3>

<p class="text-gray-700 mb-3">
The Postal Index Number (PIN) system in India uses a six-digit code to identify specific delivery regions. Each digit has meaning:
</p>

<ul class="list-disc pl-6 text-gray-700 space-y-2 mb-6">
<li><strong>First digit</strong> – Identifies the postal zone</li>
<li><strong>Second digit</strong> – Identifies the sub-zone</li>
<li><strong>Third digit</strong> – Identifies the sorting district</li>
<li><strong>Last three digits</strong> – Identify the specific delivery post office</li>
</ul>

<!-- SEARCH -->


<div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 md:p-5 mb-8">
<h2 class="text-xl font-semibold mb-3 text-indigo-700">
🔎 Search by Pincode
</h2>

<input
type="text"
id="pincodeInput"
maxlength="6"
placeholder="Enter 6-digit Pincode"
class="w-full border-2 border-indigo-400 p-3 md:p-4 rounded-lg outline-none text-base"
/>
</div>

<div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 md:p-5 mb-8">
<h2 class="text-xl font-semibold mb-3 text-indigo-700">
📍 Search by Location
</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">

<select id="stateSelect" class="p-3 border rounded-lg w-full">
<option value="">Select State</option>
</select>

<select id="districtSelect" class="p-3 border rounded-lg w-full">
<option value="">Select District</option>
</select>

<select id="officeSelect" class="p-3 border rounded-lg w-full">
<option value="">Select Post Office</option>
</select>

</div>
</div>



<!-- STATE AUTHORITY -->

<?php
echo '<div style="margin-bottom: 30px;"></div>';
echo '<div style="margin-top: 30px;"></div>';
?>
<h3 class="text-2xl font-semibold text-indigo-700 mb-3">Explore PIN Codes by State</h3>

<p class="text-gray-700 mb-5">India Post introduced the Postal Index Number system to simplify mail delivery.
Each 6-digit PIN represents a delivery post office, district and state.Select your state below to browse district-wise and office-wise PIN code listings:</p>

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-y-2 gap-x-6 text-[15px] mb-8">
<a class="text-indigo-700 hover:underline" href="/andhra-pradesh-pincode">Andhra Pradesh</a>
<a class="text-indigo-700 hover:underline" href="/assam-pincode">Assam</a>
<a class="text-indigo-700 hover:underline" href="/bihar-pincode">Bihar</a>
<a class="text-indigo-700 hover:underline" href="/chhattisgarh-pincode">Chhattisgarh</a>
<a class="text-indigo-700 hover:underline" href="/goa-pincode">Goa</a>
<a class="text-indigo-700 hover:underline" href="/gujarat-pincode">Gujarat</a>
<a class="text-indigo-700 hover:underline" href="/haryana-pincode">Haryana</a>
<a class="text-indigo-700 hover:underline" href="/himachal-pradesh-pincode">Himachal Pradesh</a>
<a class="text-indigo-700 hover:underline" href="/jharkhand-pincode">Jharkhand</a>
<a class="text-indigo-700 hover:underline" href="/karnataka-pincode">Karnataka</a>
<a class="text-indigo-700 hover:underline" href="/kerala-pincode">Kerala</a>
<a class="text-indigo-700 hover:underline" href="/madhya-pradesh-pincode">Madhya Pradesh</a>
<a class="text-indigo-700 hover:underline" href="/maharashtra-pincode">Maharashtra</a>
<a class="text-indigo-700 hover:underline" href="/odisha-pincode">Odisha</a>
<a class="text-indigo-700 hover:underline" href="/punjab-pincode">Punjab</a>
<a class="text-indigo-700 hover:underline" href="/rajasthan-pincode">Rajasthan</a>
<a class="text-indigo-700 hover:underline" href="/tamil-nadu-pincode">Tamil Nadu</a>
<a class="text-indigo-700 hover:underline" href="/telangana-pincode">Telangana</a>
<a class="text-indigo-700 hover:underline" href="/uttar-pradesh-pincode">Uttar Pradesh</a>
<a class="text-indigo-700 hover:underline" href="/west-bengal-pincode">West Bengal</a>
</div>



<h2 class="text-2xl font-bold mb-6 text-indigo-600">
📍 Browse Pincode by State
</h2>

<div class="grid md:grid-cols-2 gap-10">

<div>
<p class="text-gray-600 mb-5">
Expand a state → district → view all post offices.
</p>

<div id="stateAuthorityList" class="space-y-3"></div>
</div>

<div>
<h3 class="text-xl font-semibold text-indigo-600 mb-4">
India PIN Code Zones
</h3>

<p class="text-gray-700 mb-5">
The first two digits of a PIN code represent the postal region
managed by India Post sorting zones.
Click a zone to explore related states.
</p>

<div class="space-y-2 text-sm">

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('DELHI')">
<b>11</b> — Delhi Postal Circle
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('HARYANA')">
<b>12 – 13</b> — Haryana
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('PUNJAB')">
<b>14 – 16</b> — Punjab
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('HIMACHAL')">
<b>17</b> — Himachal Pradesh
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('JAMMU')">
<b>18 – 19</b> — Jammu & Kashmir / Ladakh
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('UTTAR PRADESH')">
<b>20 – 28</b> — Uttar Pradesh & Uttarakhand
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('RAJASTHAN')">
<b>30 – 34</b> — Rajasthan
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('GUJARAT')">
<b>36 – 39</b> — Gujarat
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('MAHARASHTRA')">
<b>40 – 44</b> — Maharashtra & Goa
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('MADHYA PRADESH')">
<b>45 – 48</b> — Madhya Pradesh
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('CHHATTISGARH')">
<b>49</b> — Chhattisgarh
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('ANDHRA')">
<b>50 – 53</b> — Andhra Pradesh & Telangana
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('KARNATAKA')">
<b>56 – 59</b> — Karnataka
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('TAMIL')">
<b>60 – 64</b> — Tamil Nadu & Puducherry
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('KERALA')">
<b>67 – 69</b> — Kerala & Lakshadweep (682)
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('WEST BENGAL')">
<b>70 – 74</b> — West Bengal
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('ANDAMAN')">
<b>744</b> — Andaman & Nicobar Islands
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('ODISHA')">
<b>75 – 77</b> — Odisha
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('ASSAM')">
<b>78</b> — Assam
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('ARUNACHAL')">
<b>79</b> — North-East States
(Arunachal Pradesh, Manipur, Meghalaya, Mizoram, Nagaland, Tripura)
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('BIHAR')">
<b>80 – 85</b> — Bihar
</div>

<div class="border p-3 rounded hover:bg-indigo-50 cursor-pointer"
onclick="scrollToState('JHARKHAND')">
<b>80 – 83, 92</b> — Jharkhand

</div>
</div>

</div>
</div>

<p class="text-gray-700 mb-6">
</p>
<h2 class="text-2xl font-semibold text-indigo-700 mb-3">Helpful Resources</h2>
<ul class="list-disc pl-6 text-gray-700 leading-8">
<li><a class="text-indigo-700 hover:underline" href="/about.php">About India Pincode Locator</a></li>
<li><a class="text-indigo-700 hover:underline" href="/editorial-policy.php">Editorial Policy</a></li>
<li><a class="text-indigo-700 hover:underline" href="/content-guidelines.php">Content Guidelines</a></li>
<li><a class="text-indigo-700 hover:underline" href="/privacy-policy.php">Privacy Policy</a></li>
<li><a class="text-indigo-700 hover:underline" href="/blog.php">Postal Guides & Articles</a></li>
<li><a class="text-indigo-700 hover:underline" href="/sitemap_index.php">XML Sitemap Index</a></li>
<li><a class="text-indigo-700 hover:underline" href="/blog-post.php?slug=what-is-pin-code-system-in-india">What Is PIN Code System In India</a></li>
<li><a class="text-indigo-700 hover:underline" href="/blog-post.php?slug=history-of-india-post-office">History Of India Post Office</a></li>
<li><a class="text-indigo-700 hover:underline" href="/blog-post.php?slug=india-postal-zones-explained">India Postal Zones Explained</a></li>
<li><a class="text-indigo-700 hover:underline" href="/blog-post.php?slug=how-speed-post-delivery-works">How Speed Post Delivery Works</a></li>
<li><a class="text-indigo-700 hover:underline" href="/blog-post.php?slug=registered-post-vs-speed-post">Registered Post vs Speed Post</a></li>
<ul class="list-disc pl-6 text-gray-700 space-y-2">
</ul>

<!-- RESULTS -->
<div id="results" class="mt-10"></div>
</section>
<!-- ===============================
END HOMEPAGE AUTHORITY CONTENT
================================== -->

</div>

</div>

</div>


<?php } ?>

<?php if($pageType=="home"): ?>
<script>

const resultsDiv=document.getElementById("results");
const stateSelect=document.getElementById("stateSelect");
const districtSelect=document.getElementById("districtSelect");
const officeSelect=document.getElementById("officeSelect");

/* PINCODE SEARCH */

document.getElementById("pincodeInput")
.addEventListener("keyup",function(){
if(this.value.length===6){
searchPincode(this.value);
}
});

async function searchPincode(pin){

resultsDiv.innerHTML="Loading...";

const res=await fetch("api.php?q="+pin);
const data=await res.json();

renderResults(data);
}

/* LOAD STATES */

async function loadStates(){

const res=await fetch("api-location.php?type=states");
const states=await res.json();

states.forEach(state=>{
if(state && state!=="nan"){
stateSelect.innerHTML+=`<option>${state}</option>`;
}
});

}
loadStates();

/* STATE CHANGE */

stateSelect.addEventListener("change",async function(){

districtSelect.innerHTML='<option>Select District</option>';

const res=await fetch(
`api-location.php?type=districts&state=${this.value}`
);

const districts=await res.json();

districts.forEach(d=>{
districtSelect.innerHTML+=`<option>${d}</option>`;
});
});

/* DISTRICT CHANGE */

districtSelect.addEventListener("change",async function(){

officeSelect.innerHTML='<option>Select Office</option>';

const res=await fetch(
`api-location.php?type=offices&state=${stateSelect.value}&district=${this.value}`
);

const offices=await res.json();

offices.forEach(o=>{
officeSelect.innerHTML+=`<option>${o}</option>`;
});
});

/* OFFICE SEARCH */

officeSelect.addEventListener("change",async function(){

resultsDiv.innerHTML="Loading...";

const res=await fetch(
`api-location.php?type=search&state=${stateSelect.value}&district=${districtSelect.value}&office=${this.value}`
);

const data=await res.json();

renderResults(data);
});

/* RESULT RENDER */

function renderResults(data){

if(!data || data.length===0){
resultsDiv.innerHTML="No results found";
return;
}

let html=`<div class="grid md:grid-cols-2 gap-6">`;

data.forEach(row=>{

let map="";

if(row.latitude && row.longitude){
map=`<a target="_blank"
class="text-indigo-600 text-sm mt-2 inline-block"
href="https://www.google.com/maps?q=${row.latitude},${row.longitude}">
📍 View Map</a>`;
}

const pincodeValue = row.pincode ?? row.Pincode ?? "";

html+=`
<div class="bg-white p-6 rounded-xl shadow">
<h3 class="font-semibold text-lg">${row.officename}</h3>
<p>${row.district}, ${row.statename}</p>
<p>Pincode: <b>${pincodeValue}</b></p>
${map}
</div>`;
});

html+="</div>";

resultsDiv.innerHTML=html;
}

/* STATE AUTHORITY */

async function loadStateAuthority(){

const res=await fetch("api-location.php?type=states");
const states=await res.json();

const container=document.getElementById("stateAuthorityList");

states.forEach(state=>{

if(!state) return;

const block=document.createElement("div");

block.innerHTML=`
<div class="border rounded-lg">
<button
class="w-full text-left p-4 font-semibold bg-white-50"
onclick="loadDistricts(this,'${state}')">
▶ ${state}
</button>
<div class="hidden p-4"></div>
</div>`;

container.appendChild(block);

});

}

loadStateAuthority();

/* LOAD DISTRICTS */

async function loadDistricts(btn,state){

const container=btn.nextElementSibling;

container.classList.toggle("hidden");

if(container.dataset.loaded) return;

container.innerHTML="Loading...";

const res=await fetch(
`api-location.php?type=districts&state=${state}`
);

const districts=await res.json();

let html="";

districts.forEach(d=>{
html+=`
<div class="border rounded mb-2">
<button class="w-full text-left p-3 bg-white-100"
onclick="loadDistrictPincodes(this,'${state}','${d}')">
📍 ${d}
</button>
<div class="hidden p-3"></div>
</div>`;
});

container.innerHTML=html;
container.dataset.loaded=true;
}

/* LOAD POST OFFICES */

async function loadDistrictPincodes(btn,state,district){

const content=btn.nextElementSibling;

content.classList.toggle("hidden");

content.innerHTML="Loading...";

const res=await fetch(
`api-location.php?type=district-postoffices&state=${state}&district=${district}`
);

const offices=await res.json();

let html=`<table class="w-full border">
<tr class="bg-white-200">
<th class="p-2">Location</th>
<th class="p-2">Pincode</th>
<th class="p-2">State</th>
<th class="p-2">District</th>
</tr>`;

offices.forEach(o=>{
html+=`
<tr class="border-b">
<td class="p-2">${o.officename}</td>
<td class="p-2">${o.pincode}</td>
<td class="p-2">${o.statename}</td>
<td class="p-2">${o.district}</td>
</tr>`;
});

html+="</table>";

content.innerHTML=html;
}

/* SCROLL */

function scrollToState(name){

document.querySelectorAll("#stateAuthorityList button")
.forEach(btn=>{
if(btn.innerText.toUpperCase().includes(name)){
btn.scrollIntoView({behavior:"smooth"});
setTimeout(()=>btn.click(),300);
}
});

}

</script>
<?php endif; ?>

</body>
</html>
