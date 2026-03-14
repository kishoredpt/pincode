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
require_once __DIR__ . "/includes/menu-pages.php";

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
$nearestRailwayContext = null;
$menuPages = getMenuPages();
$menuPageGroups = [];
foreach ($menuPages as $menuPageItem) {
    $menuPageGroups[$menuPageItem['category']][] = $menuPageItem;
}

function toSlug($value){
    $value=strtolower(trim($value));
    $value=preg_replace('/[^a-z0-9]+/','-',$value);
    return trim($value,'-');
}

$railTableCheck = $conn->query("SHOW TABLES LIKE 'railway_stations'");
$mapTableCheck = $conn->query("SHOW TABLES LIKE 'pincode_nearest_railway_station'");
$railwayTablesAvailable = ($railTableCheck && $railTableCheck->num_rows > 0 && $mapTableCheck && $mapTableCheck->num_rows > 0);

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

    if (str_starts_with($route, 'menu-')) {
        $menuSlug = substr($route, 5);
        $menuPage = getMenuPageBySlug($menuSlug);
        if ($menuPage) {
            $pageType = 'menu_page';
            $pageData = $menuPage;
        }
    }

    if ($pageType !== 'home') {
        // Already resolved by custom route handler.
    }
    else{

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

            $pinLat = $pageData[0]['latitude'] ?? null;
            $pinLon = $pageData[0]['longitude'] ?? null;

            $stmtRail = $conn->prepare("\n                SELECT rs.station_name, rs.station_code, pnr.distance_km\n                FROM pincode_nearest_railway_station pnr\n                INNER JOIN railway_stations rs ON rs.id = pnr.station_id\n                WHERE pnr.pincode = ?\n                ORDER BY pnr.distance_km ASC\n                LIMIT 1\n            ");
            if ($stmtRail) {
                $stmtRail->bind_param("s", $slug);
                $stmtRail->execute();
                $railRes = $stmtRail->get_result();
                if ($railRes && $railRes->num_rows > 0) {
                    $nearestRailwayContext = $railRes->fetch_assoc();
                }
            }

            if (
                !$nearestRailwayContext
                && is_numeric($pinLat)
                && is_numeric($pinLon)
            ) {
                $lat = (float) $pinLat;
                $lon = (float) $pinLon;
                $stmtRailFallback = $conn->prepare("\n                    SELECT station_name, station_code,\n                           ROUND(6371 * ACOS(\n                               COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) +\n                               SIN(RADIANS(?)) * SIN(RADIANS(latitude))\n                           ), 1) AS distance_km\n                    FROM railway_stations\n                    WHERE latitude IS NOT NULL AND longitude IS NOT NULL\n                    ORDER BY distance_km ASC\n                    LIMIT 1\n                ");

                if ($stmtRailFallback) {
                    $stmtRailFallback->bind_param("ddd", $lat, $lon, $lat);
                    $stmtRailFallback->execute();
                    $fallbackRes = $stmtRailFallback->get_result();
                    if ($fallbackRes && $fallbackRes->num_rows > 0) {
                        $nearestRailwayContext = $fallbackRes->fetch_assoc();
                    }
                }
            }
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

    if ($pageType === 'pincode' && $nearestRailwayContext) {
        $stationName = $nearestRailwayContext['station_name'] ?? '';
        $stationCode = $nearestRailwayContext['station_code'] ?? '';
        $distanceKm = $nearestRailwayContext['distance_km'] ?? '';
        $seoTitle = "$name Pincode Nearest Railway Station | $stationName ($stationCode)";
        $seoDescription = "Nearest railway station for PIN code $name is $stationName ($stationCode), approximately $distanceKm km away with India Post location context.";
    }

    $canonical = "https://pincodelocator.co.in/".$route;
}
elseif ($pageType === 'menu_page') {
    $seoTitle = $pageData['title'] . " | Menu Knowledge Page";
    $seoDescription = "Professional long-form map-based resource for " . $pageData['title'] . " with sections and subsections.";
    $canonical = "https://pincodelocator.co.in/" . $route;
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
  .menu-dropdown { position: relative; }
  .menu-dropdown summary { list-style: none; cursor: pointer; }
  .menu-dropdown summary::-webkit-details-marker { display: none; }
  .menu-panel {
    position: absolute;
    right: 0;
    top: calc(100% + 0.4rem);
    width: min(92vw, 64rem);
    max-height: min(72vh, 38rem);
    overflow: auto;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.9rem;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
    padding: 0.85rem;
    z-index: 20;
  }
  .menu-close-row {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 0.55rem;
  }
  .menu-close-btn {
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0.25rem 0.55rem;
    font-size: 0.75rem;
    color: #374151;
    background: #fff;
    cursor: pointer;
  }
  .menu-close-btn:hover {
    background: #f3f4f6;
    color: #111827;
  }
  .menu-section-grid {
    display: grid;
    gap: 0.75rem;
    grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr));
  }
  .menu-section {
    background: linear-gradient(135deg, #f8faff 0%, #f3f4ff 100%);
    border: 1px solid #e4e8ff;
    border-radius: 0.75rem;
    padding: 0.55rem;
  }
  .menu-section-title {
    display: block;
    padding: 0.35rem 0.5rem;
    margin-bottom: 0.3rem;
    font-size: 0.68rem;
    letter-spacing: 0.08em;
    color: #4f46e5;
    text-transform: uppercase;
    font-weight: 700;
  }
  .menu-item {
    display: block;
    padding: 0.35rem 0.55rem;
    border-radius: 0.5rem;
    text-align: left;
    transition: background-color 0.18s ease, color 0.18s ease, transform 0.18s ease;
  }
  .menu-item:hover {
    background: #eef2ff;
    color: #312e81;
    transform: translateX(2px);
  }
  @media (max-width: 767px) {
    .menu-panel {
      position: fixed;
      right: 0.6rem;
      left: 0.6rem;
      width: auto;
      top: 4.2rem;
      max-height: 74vh;
    }
  }
</style>

<div class="max-w-[1100px] mx-auto px-4 md:px-6 py-6 md:py-10">

<!-- TOP BAR -->
<div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-6">
<h1 class="text-2xl md:text-xl font-bold text-center md:text-left"><a href="/" class="inline-flex items-center gap-2 hover:text-indigo-700" aria-label="Go to home page">📮 <span>PincodeLocator.co.in</span></a></h1>

<nav class="w-full md:w-auto grid grid-cols-4 gap-2 md:flex md:items-center md:gap-5 text-sm font-medium text-center">
<a class="nav-link" href="/">Home</a>
<a class="nav-link" href="/author.php">Author</a>
<a class="nav-link" href="/contact.php">Contact</a>
<details class="menu-dropdown" id="mainMenuDropdown">
<summary class="nav-link">Main ▾</summary>
<div class="menu-panel text-sm font-medium">
<div class="menu-close-row">
<button type="button" class="menu-close-btn" id="mainMenuCloseBtn" aria-label="Close main menu">Close ✕</button>
</div>
<div class="menu-section-grid">
<div class="menu-section">
<span class="menu-section-title">Quick Links</span>
<a class="menu-item" href="/about.php">About</a>
<a class="menu-item" href="/privacy-policy.php">Privacy</a>
<a class="menu-item" href="/terms.php">Terms</a>
<a class="menu-item" href="/disclaimer.php">Disclaimer</a>
</div>
<?php foreach ($menuPageGroups as $categoryName => $groupItems): ?>
<div class="menu-section">
<span class="menu-section-title"><?= htmlspecialchars($categoryName) ?></span>
<?php foreach ($groupItems as $groupItem): ?>
<a class="menu-item" href="/menu-<?= htmlspecialchars($groupItem['slug']) ?>"><?= htmlspecialchars($groupItem['subsection']) ?> → <?= htmlspecialchars($groupItem['anchor_city']) ?></a>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</div>
</div>
</details>
</nav>
</div>

<script>
(function(){
const mainMenuDropdown=document.getElementById("mainMenuDropdown");
if(!mainMenuDropdown) return;

const closeButton=document.getElementById("mainMenuCloseBtn");
if(closeButton){
closeButton.addEventListener("click",()=>{
mainMenuDropdown.removeAttribute("open");
});
}

mainMenuDropdown.querySelectorAll(".menu-item").forEach((menuLink)=>{
menuLink.addEventListener("click",()=>{
mainMenuDropdown.removeAttribute("open");
});
});
})();
</script>

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
<div class="bg-white p-5 rounded-xl shadow w-full">
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
$districtName=trim((string)$pageData[0]['district']);
$officeName=trim((string)$pageData[0]['officename']);
?>

<h2 class="text-3xl font-bold mb-8">
Pincode <?= htmlspecialchars($pinCode) ?>
</h2>

<section class="pincode-intro bg-white rounded-xl shadow p-6 mb-8 leading-7">

<?php if($nearestRailwayContext): ?>
<?php
$railStationName = trim((string)($nearestRailwayContext['station_name'] ?? ''));
$railStationCode = trim((string)($nearestRailwayContext['station_code'] ?? ''));
$railDistance = number_format((float)($nearestRailwayContext['distance_km'] ?? 0), 1);
?>
<div class="mb-5 rounded-lg border border-indigo-200 bg-indigo-50 p-4">
  <h3 class="text-lg font-semibold text-indigo-900 mb-2">
    Nearest Railway Station for PIN Code <?= htmlspecialchars($pinCode) ?>
  </h3>
  <p class="text-gray-800 mb-1"><strong>Station:</strong> <?= htmlspecialchars($railStationName) ?></p>
  <p class="text-gray-800 mb-1"><strong>Station Code:</strong> <?= htmlspecialchars($railStationCode) ?></p>
  <p class="text-gray-800"><strong>Distance:</strong> <?= htmlspecialchars($railDistance) ?> km</p>
</div>

<div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
  <h3 class="text-base font-semibold text-emerald-900 mb-2">Nearest Railway Station Information</h3>
  <p class="text-gray-700 mb-3">
    The nearest railway station to PIN code <strong><?= htmlspecialchars($pinCode) ?></strong> is <strong><?= htmlspecialchars($railStationName) ?></strong> (<?= htmlspecialchars($railStationCode) ?>).
    This station is located approximately <strong><?= htmlspecialchars($railDistance) ?> km</strong> from the
    <strong><?= htmlspecialchars($officeName) ?></strong> post office area in
    <strong><?= htmlspecialchars($districtName) ?></strong> district of
    <strong><?= htmlspecialchars($stateName) ?></strong>.
  </p>
  <p class="text-gray-700">
    <?= htmlspecialchars($railStationName) ?> is a key railway access point for this region and helps travelers connect to major cities through the Indian Railways network.
  </p>
</div>
<?php endif; ?>

<p class="text-gray-700 mb-4">
The PIN code <strong><?= htmlspecialchars($pinCode) ?></strong> belongs to the state of <strong><?= htmlspecialchars($stateName) ?></strong>, India, and is part of the structured Postal Index Number system administered by India Post. This six-digit code helps identify the exact sorting district and delivery post office responsible for handling mail within this region.
</p>

<p class="text-gray-700 mb-4">
Every PIN code in India follows a logical hierarchy. The first digit represents the broader postal zone, the second digit indicates the sub-zone, and the first three digits together define the regional sorting district. The final three digits uniquely identify the local delivery office serving the <?= htmlspecialchars($pinCode) ?> area. This structure ensures systematic routing of letters, parcels, government notices, and commercial shipments.
</p>

<p class="text-gray-700 mb-4">
Residents, businesses, and logistics providers rely on accurate PIN code data to avoid delivery delays and address mismatches. Whether sending Speed Post, Registered Post, or e-commerce parcels, using the correct PIN code improves efficiency and reduces routing errors within the postal network.
</p>

<p class="text-gray-700">
Below, you can explore detailed information about post offices linked to <?= htmlspecialchars($pinCode) ?>, including district classification, office type, and locality coverage. For time-sensitive deliveries, users may verify operational details directly with the concerned postal office.
</p>
</section>

<div class="grid md:grid-cols-2 gap-5">

<?php foreach($pageData as $row){ ?>

<div class="bg-white p-5 rounded-xl shadow w-full">

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
elseif($pageType=="menu_page"){
    echo renderMenuPageContent($pageData);
}
else { ?>
<section class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 border border-blue-100 rounded-2xl overflow-hidden shadow-sm">
  <div class="bg-gradient-to-r from-sky-700 via-blue-600 to-indigo-700 text-white px-4 py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-2 text-sm font-semibold">
    <div class="tracking-wide">PIN Navigator • India Search Desk</div>
    <a href="/contact.php" class="underline decoration-dotted underline-offset-4">Business / Data Partnership Enquiry</a>
  </div>

  <div class="grid lg:grid-cols-[320px_1fr_290px] gap-6 p-4 md:p-6">
    <aside>
      <h2 class="text-4xl leading-tight font-extrabold mb-4 text-slate-800">Pincode Finder</h2>
      <div class="bg-gradient-to-r from-rose-600 to-orange-500 text-white font-semibold text-center py-3 rounded-lg shadow">SMART PINCODE SEARCH</div>

      <div class="space-y-3 mt-4">
        <input type="text" id="pincodeInput" maxlength="6" placeholder="Enter 6-digit Pincode" class="w-full border border-gray-300 bg-white p-3 rounded" />
        <select id="stateSelect" class="w-full border border-gray-300 bg-white p-3 rounded"><option value="">-- Select State --</option></select>
        <select id="districtSelect" class="w-full border border-gray-300 bg-white p-3 rounded"><option value="">Select District</option></select>
        <select id="officeSelect" class="w-full border border-gray-300 bg-white p-3 rounded"><option value="">Select Post Office</option></select>
      </div>

      <div id="results" class="mt-5"></div>

      <div class="mt-6 bg-white border border-emerald-200 rounded-xl p-3">
        <div class="flex items-center justify-between gap-2 mb-2">
          <h2 class="text-base font-semibold text-emerald-800">Near By Pincodes</h2>
          <button id="detectNearbyBtn" type="button" class="text-xs bg-emerald-600 text-white px-3 py-1.5 rounded hover:bg-emerald-700">Detect</button>
        </div>
        <p id="nearbyStatus" class="text-xs text-gray-600">Allow location access to find nearby post offices and pincodes.</p>
        <div id="nearbyPincodes" class="mt-2 space-y-1 text-sm"></div>
      </div>

      <div class="mt-6 border-t border-dashed border-slate-400 pt-4">
        <h3 class="text-xl font-semibold mb-2">Explore PIN Codes by State</h3>
        <div id="stateAuthorityList" class="space-y-3 max-h-[460px] overflow-auto pr-1"></div>
      </div>
    </aside>

    <main>
      <?php if($route): ?>
      <div class="text-sm text-gray-700 mb-3">
      <?php foreach($breadcrumb as $index=>$bc): ?>
      <?php if($index===0): ?><a href="<?= $bc['url'] ?>" class="hover:underline" aria-label="Home">🏠 <?= $bc['name'] ?></a><?php else: ?><a href="<?= $bc['url'] ?>" class="hover:underline"><?= $bc['name'] ?></a><?php endif; ?><?= $index < count($breadcrumb)-1 ? ' <span class="mx-1">›</span> ' : '' ?>
      <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div class="inline-flex items-center gap-2 mb-3 bg-white border border-blue-200 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold">
        <span>●</span><span>Trusted Postal Lookup</span>
      </div>
      <h3 class="text-3xl font-bold mb-3 text-slate-800">Postal Index Number Directory</h3>
      <h4 class="text-2xl font-semibold mb-3 text-indigo-900">How Pincode Search Works in India</h4>
      <div class="space-y-4 text-[28px] leading-relaxed md:text-[31px]" style="font-size:clamp(18px,1.45vw,31px)">
        <p>India has millions of delivery points, so remembering every locality PIN is not practical. This interface combines lookup tools and guidance in one workspace for faster discovery.</p>
        <p>You can search by 6-digit PIN directly or browse by state, district, and post office. The backend APIs and result rendering logic are retained exactly as before.</p>
        <p>The right-side knowledge rail connects users to existing internal pages, helping visitors continue to verified policy, informational, and regional postal resources.</p>
      </div>

      <div class="mt-8 grid md:grid-cols-2 gap-3 text-sm">
        <a href="/about.php" class="bg-white border border-gray-300 rounded px-3 py-2 hover:bg-gray-50">About</a>
        <a href="/blog.php" class="bg-white border border-gray-300 rounded px-3 py-2 hover:bg-gray-50">Postal Guides & Articles</a>
        <a href="/editorial-policy.php" class="bg-white border border-gray-300 rounded px-3 py-2 hover:bg-gray-50">Editorial Policy</a>
        <a href="/privacy-policy.php" class="bg-white border border-gray-300 rounded px-3 py-2 hover:bg-gray-50">Privacy Policy</a>
      </div>

      <div class="mt-5 bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-900">
        <strong>Advertising Disclosure:</strong> This website may display third-party ads. Sponsored content is labeled, and ad placement does not influence postal search results.
      </div>
    </main>

    <aside class="space-y-4">
      <div>
        <h3 class="text-3xl font-bold mb-2 inline-block bg-indigo-100 text-indigo-900 px-3 py-1 rounded-lg">Knowledge Hub</h3>
        <ul class="space-y-1 text-[#3f56d9] text-base">
          <?php foreach ($menuPageGroups as $categoryName => $groupItems): ?>
            <?php foreach ($groupItems as $groupItem): ?>
            <li><a class="hover:underline" href="/menu-<?= htmlspecialchars($groupItem['slug']) ?>">▸ <?= htmlspecialchars($groupItem['subsection']) ?></a></li>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </ul>
      </div>

      <div>
        <h4 class="text-2xl font-bold mb-2 inline-block bg-indigo-100 text-indigo-900 px-3 py-1 rounded-lg">Top Tourist States</h4>
        <ul class="space-y-1 text-indigo-700 text-base">
          <li><a class="hover:underline" href="/rajasthan-pincode">▸ Rajasthan</a></li>
          <li><a class="hover:underline" href="/kerala-pincode">▸ Kerala</a></li>
          <li><a class="hover:underline" href="/goa-pincode">▸ Goa</a></li>
          <li><a class="hover:underline" href="/himachal-pradesh-pincode">▸ Himachal Pradesh</a></li>
          <li><a class="hover:underline" href="/uttarakhand-pincode">▸ Uttarakhand</a></li>
          <li><a class="hover:underline" href="/tamil-nadu-pincode">▸ Tamil Nadu</a></li>
        </ul>
      </div>

      <div>
        <h4 class="text-3xl font-bold mb-2 inline-block bg-indigo-100 text-indigo-900 px-3 py-1 rounded-lg">Essential Pages</h4>
        <ul class="space-y-1 text-[#3f56d9] text-base">
          <li><a class="hover:underline" href="/contact.php">▸ Contact</a></li>
          <li><a class="hover:underline" href="/terms.php">▸ Terms</a></li>
          <li><a class="hover:underline" href="/disclaimer.php">▸ Disclaimer</a></li>
          <li><a class="hover:underline" href="/content-guidelines.php">▸ Content Guidelines</a></li>
          <li><a class="hover:underline" href="/sitemap_index.php">▸ Sitemap</a></li>
        </ul>
      </div>
    </aside>
  </div>

  <div class="border-t border-blue-100 bg-white/80 p-4 md:p-6">
    <div class="grid lg:grid-cols-2 gap-6 items-start">
      <article class="bg-white border border-slate-200 rounded-xl p-4 md:p-5 shadow-sm">
        <h3 class="text-2xl font-bold text-slate-800 mb-3">India Postal Map & Zone Visual Guide</h3>
        <p class="text-gray-700 mb-4">Explore how India Post routing works with visual references. These graphics help users understand zone digits, sorting districts, and last-mile office mapping before they search by pincode.</p>
        <div class="grid sm:grid-cols-2 gap-4">
          <figure class="border border-slate-200 rounded-lg p-2 bg-slate-50">
            <img src="/assets/images/postal-zones.svg" alt="India postal zones visual map" class="w-full h-auto">
            <figcaption class="text-xs text-gray-600 mt-2">India postal zones overview</figcaption>
          </figure>
          <figure class="border border-slate-200 rounded-lg p-2 bg-slate-50">
            <img src="/assets/images/pincode-system.svg" alt="Six digit pincode system explanation" class="w-full h-auto">
            <figcaption class="text-xs text-gray-600 mt-2">6-digit PIN code breakdown</figcaption>
          </figure>
        </div>
      </article>

      <article class="bg-gradient-to-br from-indigo-50 to-white border border-indigo-100 rounded-xl p-4 md:p-5">
        <h3 class="text-2xl font-bold text-indigo-900 mb-3">High-Intent Pages People Search Daily</h3>
        <p class="text-gray-700 mb-4">To serve millions of monthly users, this section focuses on practical postal needs: delivery checks, city-level pincode discovery, and India Post knowledge pages.</p>
        <div class="grid sm:grid-cols-2 gap-2 text-sm">
          <a href="/delhi-pincode" class="bg-white border rounded px-3 py-2 hover:bg-indigo-50">Delhi Pincode List</a>
          <a href="/maharashtra-pincode" class="bg-white border rounded px-3 py-2 hover:bg-indigo-50">Maharashtra Pincode Lookup</a>
          <a href="/karnataka-pincode" class="bg-white border rounded px-3 py-2 hover:bg-indigo-50">Karnataka Postal Areas</a>
          <a href="/telangana-pincode" class="bg-white border rounded px-3 py-2 hover:bg-indigo-50">Telangana Office Finder</a>
          <a href="/blog-post.php?slug=india-postal-zones-explained" class="bg-white border rounded px-3 py-2 hover:bg-indigo-50">Understand Postal Zones</a>
          <a href="/blog-post.php?slug=how-speed-post-delivery-works" class="bg-white border rounded px-3 py-2 hover:bg-indigo-50">Speed Post Delivery Guide</a>
        </div>
      </article>
    </div>

    <div class="mt-6 grid md:grid-cols-2 gap-4">
      <section class="bg-white border border-dashed border-slate-300 rounded-xl p-4 text-center">
        <p class="text-xs uppercase tracking-widest text-slate-500 mb-2">Ad Space</p>
        <div class="min-h-[120px] grid place-items-center bg-slate-50 rounded border border-slate-200 text-slate-500">
          Google AdSense Unit (Responsive)
        </div>
      </section>
      <section class="bg-white border border-dashed border-slate-300 rounded-xl p-4 text-center">
        <p class="text-xs uppercase tracking-widest text-slate-500 mb-2">Ad Space</p>
        <div class="min-h-[120px] grid place-items-center bg-slate-50 rounded border border-slate-200 text-slate-500">
          Google AdSense Unit (In-content)
        </div>
      </section>
    </div>

    <div class="mt-6 grid lg:grid-cols-3 gap-4 text-sm">
      <section class="bg-white border border-slate-200 rounded-xl p-4">
        <h4 class="font-bold text-slate-800 mb-2">Why users trust this platform</h4>
        <ul class="list-disc pl-5 text-gray-700 space-y-1">
          <li>State → district → office browsing with instant results.</li>
          <li>One-click pincode validation for shipping and forms.</li>
          <li>Guides and policies linked transparently for reliability.</li>
        </ul>
      </section>
      <section class="bg-white border border-slate-200 rounded-xl p-4">
        <h4 class="font-bold text-slate-800 mb-2">Popular use-cases</h4>
        <ul class="list-disc pl-5 text-gray-700 space-y-1">
          <li>E-commerce delivery address verification.</li>
          <li>Banking, KYC, and government form completion.</li>
          <li>Courier network planning and serviceability checks.</li>
        </ul>
      </section>
      <section class="bg-white border border-slate-200 rounded-xl p-4">
        <h4 class="font-bold text-slate-800 mb-2">Grow with us</h4>
        <p class="text-gray-700 mb-3">Have bulk postal data, a logistics partnership idea, or want city-specific landing pages? We actively collaborate on utility-first postal products.</p>
        <a href="/contact.php" class="inline-block bg-indigo-600 text-white px-3 py-2 rounded hover:bg-indigo-700">Contact Team</a>
      </section>
    </div>

    <section class="mt-6 bg-white border border-slate-200 rounded-xl p-4 md:p-6">
      <div class="grid lg:grid-cols-2 gap-6 items-start">
        <figure class="bg-slate-50 border border-slate-200 rounded-xl p-3">
          <img src="/assets/images/pincode-dot-map.svg" alt="India state map showing 2-digit PIN prefixes used in postal routing" class="w-full h-auto rounded-lg">
          <figcaption class="text-xs text-gray-600 mt-3">India 2-digit PIN prefix map highlighting state-wise postal routing patterns.</figcaption>
        </figure>
        <article>
          <h3 class="text-2xl md:text-3xl font-bold text-slate-900 mb-3">India State PIN Prefix Map Context</h3>
          <p class="text-gray-700 text-sm md:text-base leading-7 mb-4">This India pincode dot map is designed as a practical reading aid for people who need to make quick location decisions, not just a decorative visual. Every dot hints at how postal identity travels from national sorting logic to local delivery routes, and that makes the graphic valuable for buyers, sellers, support teams, and operations planners alike. When users look at plain text pincode lists, they often miss pattern relationships between neighboring districts, corridor towns, and edge regions where service speed can differ. A map view closes that gap instantly because it gives structure to what would otherwise be scattered numbers. The map encourages users to think in flows: where mail enters a zone, where it branches, and where it reaches the final office. This is especially useful when a person is validating delivery promises, onboarding new addresses, or auditing service coverage across multiple cities at once. In that sense, the graphic is not replacing search, but strengthening search. It gives context before the query, confidence during the query, and interpretation after the result appears, which is exactly what a postal intelligence surface should do for high-intent users. It also helps teams compare seasonal demand clusters, understand corridor pressure, and plan realistic delivery communication instead of relying on broad assumptions across diverse postal geographies and customer expectations. Another reason this map matters is trust. Users tend to trust systems that explain themselves, and visual context is one of the fastest ways to create that explanation. A person checking a pincode for banking KYC, insurance forms, school admissions, or courier dispatch is often under time pressure and cannot afford ambiguity. By placing this map before the intelligence section, the page now tells a clear story: first understand national distribution, then move into deeper postal insights and actions. That sequencing reduces cognitive load because people see the big picture before reading metrics and feature blocks. It also supports better decision quality in borderline cases, such as addresses near district boundaries, newly expanded municipal zones, or similar locality names across states. When users see India as a connected postal field rather than isolated records, they are less likely to assume that one successful delivery proves all neighboring locations are equally serviceable. Instead, they develop healthier verification behavior: check pincode, confirm post office, compare district, and then proceed. Over time, that behavior lowers failed shipments, fewer return-to-origin events, and less support friction for businesses. A simple graphic can therefore create measurable operational benefits when positioned correctly and explained with purpose. From an educational perspective, the dot map also helps decode the six-digit PIN system in a way text alone cannot. The first digit indicates region, the second narrows the sub-region, and the third points to sorting district context, while the final three digits identify the destination office. Many users memorize this sequence abstractly but still struggle to apply it when comparing addresses across unfamiliar geographies. With the map beside them, the idea becomes tangible: they can imagine how one digit shift may move mail into a different processing lane even if place names sound nearby. That understanding is critical for marketplaces and fulfillment teams managing thousands of orders where small addressing errors multiply into real costs. It is equally important for residents in rapidly changing peri-urban belts, where legacy locality naming and new administrative updates can overlap. By combining the dot map with searchable records, the page acts like a practical classroom: visual orientation first, exact verification second, execution third. This layered model supports beginners and power users at the same time. Beginners gain confidence through pattern recognition, while experienced users get faster triage when comparing high-volume address lists. Finally, this map should be read as a living context panel, not a fixed claim about real-time transport conditions. Postal performance can shift due to weather, festival load, infrastructure work, and local operational constraints, so no single visual should be treated as a guarantee. The right way to use this section is as a strategic compass: identify probable coverage direction, understand relative spread, and then validate the exact pincode and office through the lookup pathways on this site. That approach protects users from overconfidence while still giving them speed. It also aligns with responsible information design, where visuals guide interpretation but detailed data confirms action. By adding the India pincode dot map before the Intelligence Desk, the homepage now balances narrative, orientation, and utility in a single flow. Users can move from macro awareness to micro verification without jumping across disconnected pages, which improves continuity and makes the platform feel purpose-built for real postal tasks. Whether someone is sending a single personal parcel, managing enterprise dispatch, or researching regional service access, this section provides a unique, human-readable context layer that turns postal data into practical decision support.</p>
        </article>
      </div>
    </section>

    <div class="mt-6 bg-gradient-to-r from-slate-900 via-indigo-900 to-blue-900 text-white rounded-xl p-4 md:p-5">
      <h3 class="text-xl md:text-2xl font-bold mb-2">India Pincode Intelligence Desk</h3>
      <p class="text-blue-100 text-sm md:text-base">Built for scale: discover postal data for metro cities, fast-growing districts, and high-commerce corridors through one search experience.</p>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4 text-sm">
        <div class="bg-white/10 rounded-lg p-3"><b class="block text-lg">1.5L+</b><span>Post offices indexed</span></div>
        <div class="bg-white/10 rounded-lg p-3"><b class="block text-lg">28+</b><span>States covered</span></div>
        <div class="bg-white/10 rounded-lg p-3"><b class="block text-lg">700+</b><span>District clusters</span></div>
        <div class="bg-white/10 rounded-lg p-3"><b class="block text-lg">24×7</b><span>Search availability</span></div>
      </div>
    </div>

    <div class="mt-6 grid md:grid-cols-3 gap-4 text-sm">
      <a href="/about.php" class="bg-white border border-slate-200 rounded-xl p-4 hover:bg-slate-50"><b>About Us</b><p class="text-gray-600 mt-1">Know our mission and data coverage.</p></a>
      <a href="/data-source.php" class="bg-white border border-slate-200 rounded-xl p-4 hover:bg-slate-50"><b>Data Source</b><p class="text-gray-600 mt-1">How postal data is sourced and updated.</p></a>
      <a href="/contact.php" class="bg-white border border-slate-200 rounded-xl p-4 hover:bg-slate-50"><b>Contact & Corrections</b><p class="text-gray-600 mt-1">Report updates or postal data issues.</p></a>
    </div>

    <div class="mt-6 grid lg:grid-cols-3 gap-4">
      <section class="bg-white border border-slate-200 rounded-xl p-4">
        <h4 class="text-lg font-bold text-slate-800 mb-3">Top City Searches</h4>
        <ul class="grid grid-cols-2 gap-2 text-sm text-indigo-700">
          <li><a class="hover:underline" href="/delhi-pincode">Delhi</a></li>
          <li><a class="hover:underline" href="/maharashtra-pincode">Maharashtra</a></li>
          <li><a class="hover:underline" href="/karnataka-pincode">Karnataka</a></li>
          <li><a class="hover:underline" href="/tamil-nadu-pincode">Tamil Nadu</a></li>
          <li><a class="hover:underline" href="/west-bengal-pincode">West Bengal</a></li>
          <li><a class="hover:underline" href="/telangana-pincode">Telangana</a></li>
          <li><a class="hover:underline" href="/gujarat-pincode">Gujarat</a></li>
          <li><a class="hover:underline" href="/uttar-pradesh-pincode">Uttar Pradesh</a></li>
        </ul>
      </section>

      <section class="bg-white border border-slate-200 rounded-xl p-4 lg:col-span-2">
        <h4 class="text-lg font-bold text-slate-800 mb-3">Frequently Asked Pincode Questions</h4>
        <div class="grid md:grid-cols-2 gap-3 text-sm text-gray-700">
          <details class="border rounded-lg p-3 bg-slate-50"><summary class="font-semibold cursor-pointer">How do I verify a correct pincode quickly?</summary><p class="mt-2">Use the PIN search box for direct checks, or choose State → District → Post Office to confirm the final delivery office.</p></details>
          <details class="border rounded-lg p-3 bg-slate-50"><summary class="font-semibold cursor-pointer">Can one area have multiple post offices?</summary><p class="mt-2">Yes. A single pincode may include multiple branch/sub offices depending on delivery routes and administrative structure.</p></details>
          <details class="border rounded-lg p-3 bg-slate-50"><summary class="font-semibold cursor-pointer">Why does pincode matter for e-commerce?</summary><p class="mt-2">Logistics partners use pincodes for serviceability, ETA calculation, warehouse mapping, and preventing failed deliveries.</p></details>
          <details class="border rounded-lg p-3 bg-slate-50"><summary class="font-semibold cursor-pointer">Is this useful for KYC and bank forms?</summary><p class="mt-2">Absolutely. Accurate pincode-office mapping helps avoid rejection in KYC, insurance, and government documentation.</p></details>
        </div>
      </section>
    </div>
  </div>
</section>

<?php } ?>

<?php if($pageType=="home"): ?>
<script>

const resultsDiv=document.getElementById("results");
const stateSelect=document.getElementById("stateSelect");
const districtSelect=document.getElementById("districtSelect");
const officeSelect=document.getElementById("officeSelect");
const detectNearbyBtn=document.getElementById("detectNearbyBtn");
const nearbyStatus=document.getElementById("nearbyStatus");
const nearbyPincodes=document.getElementById("nearbyPincodes");

/* PINCODE SEARCH */

document.getElementById("pincodeInput")
.addEventListener("input",function(){
const pin=this.value.trim();

if(pin.length===0){
resultsDiv.innerHTML="";
return;
}

if(pin.length<6){
return;
}

if(!/^\d{6}$/.test(pin)){
resultsDiv.innerHTML="Please enter a valid 6-digit pincode.";
return;
}

searchPincode(pin);
});

async function searchPincode(pin){

resultsDiv.innerHTML="Loading...";

const res=await fetch(`api.php?q=${encodeURIComponent(pin)}`);
const data=await res.json();

if(!res.ok){
resultsDiv.innerHTML=data?.error || "Unable to fetch pincode details right now.";
return;
}

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

if(!Array.isArray(data) || data.length===0){
resultsDiv.innerHTML="No results found";
return;
}

let html=`<div class="grid grid-cols-1 gap-4">`;

const firstRow=data[0] || {};
const nearestName=firstRow.nearest_station_name || "";
const nearestCode=firstRow.nearest_station_code || "";
const nearestDistanceRaw=firstRow.nearest_station_distance_km;
const nearestDistance=
nearestDistanceRaw !== undefined && nearestDistanceRaw !== null && nearestDistanceRaw !== ""
? Number(nearestDistanceRaw).toFixed(1)
: "";

if(nearestName && nearestCode){
html+=`
<div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
  <h3 class="font-semibold text-indigo-900">Nearest Railway Station</h3>
  <p class="text-sm text-gray-800 mt-1">Station: <b>${escapeHtml(nearestName)}</b> (${escapeHtml(nearestCode)})</p>
  <p class="text-sm text-gray-800">Approx Distance: <b>${escapeHtml(nearestDistance)}</b> km</p>
</div>`;
}

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
<div class="bg-white p-5 rounded-xl shadow w-full">
<h3 class="font-semibold text-lg">${escapeHtml(row.officename || "")}</h3>
<p>${escapeHtml(row.district || "")}, ${escapeHtml(row.statename || "")}</p>
<p>Pincode: <b>${escapeHtml(pincodeValue)}</b></p>
${map}
</div>`;
});

html+="</div>";

resultsDiv.innerHTML=html;
}

function escapeHtml(value){
return String(value)
.replace(/&/g,"&amp;")
.replace(/</g,"&lt;")
.replace(/>/g,"&gt;")
.replace(/\"/g,"&quot;")
.replace(/'/g,"&#039;");
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

function slugify(value){
return String(value||"")
.toLowerCase()
.trim()
.replace(/[^a-z0-9]+/g,"-")
.replace(/^-+|-+$/g,"");
}

async function detectNearbyPincodes(){
if(!nearbyStatus || !nearbyPincodes) return;

if(!navigator.geolocation){
nearbyStatus.textContent="Geolocation is not supported in this browser.";
return;
}

nearbyStatus.textContent="Detecting your location...";
nearbyPincodes.innerHTML="";

let position;
try{
position=await new Promise((resolve,reject)=>{
navigator.geolocation.getCurrentPosition(resolve,reject,{timeout:12000,enableHighAccuracy:true});
});
}catch(_err){
nearbyStatus.textContent="Location permission denied or unavailable. You can still search manually.";
return;
}

const lat=position.coords.latitude;
const lon=position.coords.longitude;

try{
const geoRes=await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`);
const geoData=await geoRes.json();
const addr=geoData?.address || {};

const rawState=addr.state || addr.region || "";
const rawDistrict=addr.state_district || addr.county || addr.city_district || addr.city || "";

if(!rawState || !rawDistrict){
nearbyStatus.textContent="Could not map your location to a district/state. Try search by state and district.";
return;
}

const cleanDistrict=rawDistrict.replace(/\s+district$/i,"").trim();
const res=await fetch(`api-location.php?type=district-postoffices&state=${encodeURIComponent(rawState)}&district=${encodeURIComponent(cleanDistrict)}`);
const offices=await res.json();

if(!Array.isArray(offices) || offices.length===0){
nearbyStatus.textContent=`No nearby office list found for ${cleanDistrict}, ${rawState}.`;
return;
}

nearbyStatus.textContent=`Showing nearby entries for ${cleanDistrict}, ${rawState}`;

const top=offices.slice(0,8);
nearbyPincodes.innerHTML=top.map((office)=>{
const pincode=office.pincode || "";
const officeName=office.officename || "Post Office";
const route=`/${slugify(officeName)}-post-office-${pincode}`;
return `<a class="block bg-emerald-50 border border-emerald-100 rounded px-2 py-1 hover:bg-emerald-100" href="${route}">${officeName} - ${pincode}</a>`;
}).join("");
}catch(_err){
nearbyStatus.textContent="Unable to auto-detect nearby pincodes right now.";
}
}

if(detectNearbyBtn){
detectNearbyBtn.addEventListener("click",detectNearbyPincodes);
}

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
