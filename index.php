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

define("ALLOW_DB_OPTIONAL", true);
require_once "config/db.php";
require_once __DIR__ . "/includes/menu-pages.php";
require_once __DIR__ . "/includes/slug.php";
require_once __DIR__ . "/includes/site-settings.php";

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
    return slugify_text($value);
}

function pickVariant(array $variants, int $seed, int $offset = 0): string {
    if(empty($variants)){
        return '';
    }
    $index = abs($seed + $offset) % count($variants);
    return $variants[$index];
}

function uniqueList(array $items, int $limit = 8): array {
    $seen = [];
    $result = [];
    foreach($items as $item){
        $value = trim((string)$item);
        if($value === ''){
            continue;
        }
        $key = mb_strtolower($value);
        if(isset($seen[$key])){
            continue;
        }
        $seen[$key] = true;
        $result[] = $value;
        if(count($result) >= $limit){
            break;
        }
    }
    return $result;
}

function getNearbyStatesFor(string $stateName, array $fallbackStates = []): array {
    $map = [
        'andhra pradesh' => ['Telangana', 'Karnataka', 'Tamil Nadu', 'Odisha', 'Chhattisgarh'],
        'arunachal pradesh' => ['Assam', 'Nagaland'],
        'assam' => ['Arunachal Pradesh', 'Nagaland', 'Meghalaya', 'West Bengal'],
        'bihar' => ['Uttar Pradesh', 'Jharkhand', 'West Bengal'],
        'chhattisgarh' => ['Madhya Pradesh', 'Maharashtra', 'Odisha', 'Telangana'],
        'delhi' => ['Haryana', 'Uttar Pradesh', 'Rajasthan'],
        'goa' => ['Maharashtra', 'Karnataka'],
        'gujarat' => ['Rajasthan', 'Madhya Pradesh', 'Maharashtra'],
        'haryana' => ['Punjab', 'Delhi', 'Rajasthan', 'Uttar Pradesh'],
        'himachal pradesh' => ['Punjab', 'Haryana', 'Uttarakhand'],
        'jharkhand' => ['Bihar', 'West Bengal', 'Odisha', 'Chhattisgarh'],
        'karnataka' => ['Maharashtra', 'Goa', 'Kerala', 'Tamil Nadu', 'Telangana', 'Andhra Pradesh'],
        'kerala' => ['Tamil Nadu', 'Karnataka'],
        'madhya pradesh' => ['Uttar Pradesh', 'Rajasthan', 'Gujarat', 'Maharashtra', 'Chhattisgarh'],
        'maharashtra' => ['Gujarat', 'Madhya Pradesh', 'Chhattisgarh', 'Telangana', 'Karnataka', 'Goa'],
        'odisha' => ['West Bengal', 'Jharkhand', 'Chhattisgarh', 'Andhra Pradesh'],
        'punjab' => ['Haryana', 'Himachal Pradesh', 'Rajasthan'],
        'rajasthan' => ['Punjab', 'Haryana', 'Uttar Pradesh', 'Madhya Pradesh', 'Gujarat'],
        'tamil nadu' => ['Kerala', 'Karnataka', 'Andhra Pradesh'],
        'telangana' => ['Maharashtra', 'Chhattisgarh', 'Karnataka', 'Andhra Pradesh'],
        'uttar pradesh' => ['Uttarakhand', 'Haryana', 'Delhi', 'Rajasthan', 'Madhya Pradesh', 'Bihar'],
        'uttarakhand' => ['Uttar Pradesh', 'Himachal Pradesh', 'Haryana'],
        'west bengal' => ['Jharkhand', 'Bihar', 'Odisha', 'Assam'],
    ];

    $key = mb_strtolower(trim($stateName));
    if(isset($map[$key])){
        return uniqueList($map[$key], 6);
    }

    return uniqueList($fallbackStates, 6);
}

$dbAvailable = $conn instanceof mysqli;
$railwayTablesAvailable = false;

if($dbAvailable){
    $railTableCheck = $conn->query("SHOW TABLES LIKE 'railway_stations'");
    $mapTableCheck = $conn->query("SHOW TABLES LIKE 'pincode_nearest_railway_station'");
    $railwayTablesAvailable = ($railTableCheck && $railTableCheck->num_rows > 0 && $mapTableCheck && $mapTableCheck->num_rows > 0);
}

/* =========================
ROUTE ENGINE
========================= */

if(!$dbAvailable){
    if($route && !str_starts_with($route, 'menu-')){
        $metaRobots = 'noindex, follow';
    }
}
elseif($route && preg_match('/^nearest-railway-station-(\d{6})$/',$route,$railRouteMatch)) {

    $railPincode = $railRouteMatch[1];
    $stmt = $conn->prepare("
        SELECT pincode, officename, district, statename, latitude, longitude
        FROM post_offices
        WHERE pincode = ?
        ORDER BY officename
        LIMIT 1
    ");

    if ($stmt) {
        $stmt->bind_param("s", $railPincode);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $pinRow = $res->fetch_assoc();
            $nearestRailway = null;

            $stmtMappedRail = $conn->prepare("
                SELECT rs.station_name, rs.station_code, pnr.distance_km
                FROM pincode_nearest_railway_station pnr
                INNER JOIN railway_stations rs ON rs.id = pnr.station_id
                WHERE pnr.pincode = ?
                ORDER BY pnr.distance_km ASC
                LIMIT 1
            ");

            if ($stmtMappedRail) {
                $stmtMappedRail->bind_param("s", $railPincode);
                $stmtMappedRail->execute();
                $mappedRailRes = $stmtMappedRail->get_result();
                if ($mappedRailRes && $mappedRailRes->num_rows > 0) {
                    $nearestRailway = $mappedRailRes->fetch_assoc();
                }
            }

            if (!$nearestRailway && is_numeric($pinRow['latitude'] ?? null) && is_numeric($pinRow['longitude'] ?? null)) {
                $lat = (float) $pinRow['latitude'];
                $lon = (float) $pinRow['longitude'];
                $stmtRailFallback = $conn->prepare("
                    SELECT station_name, station_code,
                           ROUND(6371 * ACOS(
                               COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) +
                               SIN(RADIANS(?)) * SIN(RADIANS(latitude))
                           ), 1) AS distance_km
                    FROM railway_stations
                    WHERE latitude IS NOT NULL AND longitude IS NOT NULL
                    ORDER BY distance_km ASC
                    LIMIT 1
                ");

                if ($stmtRailFallback) {
                    $stmtRailFallback->bind_param("ddd", $lat, $lon, $lat);
                    $stmtRailFallback->execute();
                    $fallbackRailRes = $stmtRailFallback->get_result();
                    if ($fallbackRailRes && $fallbackRailRes->num_rows > 0) {
                        $nearestRailway = $fallbackRailRes->fetch_assoc();
                    }
                }
            }

            if ($nearestRailway) {
                $pageType = "railway_pin";
                $pageData = [
                    "pincode" => $railPincode,
                    "officename" => $pinRow['officename'] ?? '',
                    "district" => $pinRow['district'] ?? '',
                    "statename" => $pinRow['statename'] ?? '',
                    "station_name" => $nearestRailway['station_name'] ?? '',
                    "station_code" => $nearestRailway['station_code'] ?? '',
                    "distance_km" => $nearestRailway['distance_km'] ?? ''
                ];
            }
        }
    }
}
elseif($route && preg_match('/^(.+)-post-office-(\d{6})$/',$route,$officeMatch)){

    $officeSlug=(string)$officeMatch[1];
    $pincode=$officeMatch[2];

    $stmt=$conn->prepare("\n        SELECT *\n        FROM post_offices\n        WHERE pincode=?\n        ORDER BY officename\n        LIMIT 100\n    ");
    $stmt->bind_param("s",$pincode);
    $stmt->execute();
    $res=$stmt->get_result();

    $officeMatchRow=null;
    $candidateRows=[];

    while($row=$res->fetch_assoc()){
        $rowSlug = toSlug($row['officename'] ?? '');
        if($rowSlug===''){
            continue;
        }
        $candidateRows[] = ['row' => $row, 'slug' => $rowSlug];

        if($rowSlug===$officeSlug){
            $officeMatchRow=$row;
            break;
        }
    }

    if(!$officeMatchRow){
        $normalizedOfficeSlug = toSlug(str_replace('-', ' ', $officeSlug));
        if($normalizedOfficeSlug !== '' && !is_malformed_office_slug($normalizedOfficeSlug)){
            $bestCandidate = null;
            $bestDistance = null;

            foreach($candidateRows as $candidate){
                $distance = levenshtein($normalizedOfficeSlug, $candidate['slug']);
                if($bestDistance===null || $distance < $bestDistance){
                    $bestDistance = $distance;
                    $bestCandidate = $candidate;
                }
            }

            if($bestCandidate && $bestDistance !== null && $bestDistance <= 2){
                $redirectUrl = '/'.$bestCandidate['slug'].'-post-office-'.$pincode;
                header('Location: '.$redirectUrl, true, 301);
                exit;
            }
        }
    }

    if($officeMatchRow){
        $canonicalSlug = toSlug($officeMatchRow['officename'] ?? '');
        if($canonicalSlug !== '' && $officeSlug !== $canonicalSlug){
            header('Location: /'.$canonicalSlug.'-post-office-'.$pincode, true, 301);
            exit;
        }

        $pageType="office";
        $pageData=$officeMatchRow;
    }
    else{
        http_response_code(404);
        require __DIR__."/404.php";
        exit;
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

            if (is_numeric($pinLat) && is_numeric($pinLon)) {
                $lat = (float) $pinLat;
                $lon = (float) $pinLon;
                $stmtRailFallback = $conn->prepare("\n                    SELECT station_name, station_code,\n                           ROUND(6371 * ACOS(\n                               COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) +\n                               SIN(RADIANS(?)) * SIN(RADIANS(latitude))\n                           ), 1) AS distance_km\n                    FROM railway_stations\n                    WHERE latitude IS NOT NULL AND longitude IS NOT NULL\n                    ORDER BY distance_km ASC\n                    LIMIT 1\n                ");

                if ($stmtRailFallback) {
                    $stmtRailFallback->bind_param("ddd", $lat, $lon, $lat);
                    $stmtRailFallback->execute();
                    $fallbackRes = $stmtRailFallback->get_result();
                    if ($fallbackRes && $fallbackRes->num_rows > 0) {
                        $fallbackNearest = $fallbackRes->fetch_assoc();
                        $mappedDistance = isset($nearestRailwayContext['distance_km']) ? (float)$nearestRailwayContext['distance_km'] : null;
                        $fallbackDistance = isset($fallbackNearest['distance_km']) ? (float)$fallbackNearest['distance_km'] : null;

                        if (
                            !$nearestRailwayContext
                            || !is_numeric($mappedDistance)
                            || (is_numeric($fallbackDistance) && $fallbackDistance + 1 < $mappedDistance)
                        ) {
                            $nearestRailwayContext = $fallbackNearest;
                        }
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

if($pageType=="office"){
    $officeState = trim((string)($pageData['statename'] ?? ''));
    $officeDistrict = trim((string)($pageData['district'] ?? ''));
    $officeNameForCrumb = trim((string)($pageData['officename'] ?? ''));

    if($officeState!==''){
        $stateRoute = toSlug($officeState)."-pincode";
        $breadcrumb[] = [
            "name" => $officeState,
            "url" => "https://pincodelocator.co.in/".$stateRoute
        ];
    }

    if($officeDistrict!==''){
        $districtRoute = toSlug($officeDistrict)."-pincode";
        $breadcrumb[] = [
            "name" => $officeDistrict,
            "url" => "https://pincodelocator.co.in/".$districtRoute
        ];
    }

    if($officeNameForCrumb!==''){
        $breadcrumb[] = [
            "name" => $officeNameForCrumb." Post Office",
            "url" => "https://pincodelocator.co.in/".$route
        ];
    }
}

$seoTitle = "India Pincode Search – Find Post Office, District & State";
$seoDescription = "Search Indian PIN Codes, Post Offices, Districts and States across India using official postal data.";

$canonical = "https://pincodelocator.co.in/";
$metaRobots = "index, follow";

$gscVerification = gsc_site_verification();
$ga4Id = ga4_measurement_id();

/* STATE PAGE */
if($pageType=="office"){

    $seoTitle = $pageData['officename']." Post Office (".$pageData['pincode'].") | "
        .$pageData['district'].", ".$pageData['statename'];
    $seoDescription = "Postal details for ".$pageData['officename']
        ." Post Office, pincode ".$pageData['pincode']
        ." in ".$pageData['district'].", ".$pageData['statename'].".";
    $canonical = "https://pincodelocator.co.in/".$route;
    $metaRobots = "noindex, follow";
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
elseif($pageType === "railway_pin") {

    $pin = $pageData['pincode'] ?? '';
    $stationName = trim((string)($pageData['station_name'] ?? ''));
    $stationCode = trim((string)($pageData['station_code'] ?? ''));
    $distanceKm = $pageData['distance_km'] ?? '';
    $districtName = trim((string)($pageData['district'] ?? ''));
    $stateName = trim((string)($pageData['statename'] ?? ''));

    $seoTitle = "Nearest Railway Station for $pin PIN Code | $stationName ($stationCode)";
    $seoDescription = "Find the nearest railway station to PIN code $pin in $districtName, $stateName. Includes station code, approximate distance, and postal coverage context for address planning.";
    $canonical = "https://pincodelocator.co.in/".$route;
}
elseif ($pageType === 'menu_page') {
    $seoTitle = $pageData['title'] . " | Menu Knowledge Page";
    $seoDescription = "Professional long-form map-based resource for " . $pageData['title'] . " with sections and subsections.";
    $canonical = "https://pincodelocator.co.in/" . $route;
    $metaRobots = "noindex, follow";
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

<meta property="og:type" content="website">
<meta property="og:site_name" content="PincodeLocator.co.in">
<meta property="og:title" content="<?= htmlspecialchars($seoTitle, ENT_QUOTES, "UTF-8") ?>">
<meta property="og:description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, "UTF-8") ?>">
<meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, "UTF-8") ?>">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($seoTitle, ENT_QUOTES, "UTF-8") ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, "UTF-8") ?>">

<?php if($gscVerification !== ''): ?>
<meta name="google-site-verification" content="<?= htmlspecialchars($gscVerification, ENT_QUOTES, "UTF-8") ?>">
<?php endif; ?>

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

<script type="application/ld+json">
{
 "@context": "https://schema.org",
 "@type": "WebSite",
 "name": "PincodeLocator.co.in",
 "url": "https://pincodelocator.co.in/",
 "potentialAction": {
   "@type": "SearchAction",
   "target": "https://pincodelocator.co.in/{search_term_string}",
   "query-input": "required name=search_term_string"
 }
}
</script>

<?php if($ga4Id !== ''): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($ga4Id, ENT_QUOTES, "UTF-8") ?>"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '<?= htmlspecialchars($ga4Id, ENT_QUOTES, "UTF-8") ?>');
</script>
<?php endif; ?>

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

  .home-side-ad {
    position: fixed;
    top: 12rem;
    width: 12.5rem;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 0.85rem;
    box-shadow: 0 16px 35px rgba(15, 23, 42, 0.12);
    z-index: 30;
    overflow: hidden;
  }
  .home-side-ad a {
    display: block;
    text-decoration: none;
    color: #111827;
    padding: 1rem;
  }
  .home-side-ad-left { left: 1rem; }
  .home-side-ad-right { right: 1rem; }
  .home-ad-badge {
    display: inline-block;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #4f46e5;
    background: #eef2ff;
    padding: 0.1rem 0.45rem;
    border-radius: 999px;
    margin-bottom: 0.45rem;
  }
  .home-side-ad h3 {
    font-size: 1.25rem;
    line-height: 1.15;
    margin: 0.25rem 0;
  }
  .home-side-ad .ad-brand {
    display: inline-block;
    font-weight: 800;
    background: linear-gradient(90deg, #f59e0b 0%, #ec4899 52%, #3b82f6 100%);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.45);
  }
  .home-side-ad p {
    margin: 0.35rem 0;
    font-size: 0.83rem;
    line-height: 1.3;
  }
  .home-ad-highlight {
    color: #4f46e5;
    font-weight: 600;
  }
  .home-ad-images {
    display: grid;
    gap: 0.45rem;
    margin-top: 0.55rem;
  }
  .home-ad-images img {
    width: 100%;
    aspect-ratio: 4 / 3;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid rgba(79, 70, 229, 0.22);
    background: #f8fafc;
  }
  @media (max-width: 1360px) {
    .home-side-ad { display: none; }
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



<div class="home-side-ad home-side-ad-left" aria-label="Happy Heavens Farm Houses advertisement">
<a href="https://www.instagram.com/svcs_infradevelopers?igsh=MW10bDFxYmhjdnNzNQ==" title="Happy Heavens Farm Houses at Aler" target="_blank" rel="noopener noreferrer">
<span class="home-ad-badge">Sponsored</span>
<h3><span class="ad-brand">Happy Heavens</span><br><span class="ad-brand">Farm Houses</span></h3>
<p>at Aler • SVCS Infra Developers</p>
<p class="home-ad-highlight">Own your dream farmhouse with ultra luxury features.</p>
<div class="home-ad-images" aria-label="Happy Heavens Farm Houses ad gallery">
<img src="/assets/images/ads/happy-heavens-ad-1.jpg" alt="Happy Heavens Farm House advertisement artwork" loading="lazy">
<img src="/assets/images/ads/happy-heavens-ad-2.jpg" alt="Happy Heavens Farm House sample villa" loading="lazy">
</div>
</a>
</div>

<div class="home-side-ad home-side-ad-right" aria-label="Happy Heavens Farm Houses advertisement">
<a href="https://www.instagram.com/svcs_infradevelopers?igsh=MW10bDFxYmhjdnNzNQ==" title="Happy Heavens Farm Houses at Aler" target="_blank" rel="noopener noreferrer">
<span class="home-ad-badge">Sponsored</span>
<h3><span class="ad-brand">Happy Heavens</span><br><span class="ad-brand">Farm Houses</span></h3>
<p>at Aler • SVCS Infra Developers</p>
<p class="home-ad-highlight">Visit Aler farmhouse project.</p>
<div class="home-ad-images" aria-label="Happy Heavens Farm Houses ad gallery">
<img src="/assets/images/ads/happy-heavens-ad-1.jpg" alt="Happy Heavens Farm House advertisement artwork" loading="lazy">
<img src="/assets/images/ads/happy-heavens-ad-2.jpg" alt="Happy Heavens Farm House sample villa" loading="lazy">
</div>
</a>
</div>

<div class="max-w-[1100px] mx-auto px-4 md:px-6 py-6 md:py-10">

<!-- TOP BAR -->
<div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-6">
<div class="text-2xl md:text-xl font-bold text-center md:text-left"><a href="/" class="inline-flex items-center gap-2 hover:text-indigo-700" aria-label="Go to home page">📮 <span>PincodeLocator.co.in</span></a></div>

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

<?php if($route): ?>
<nav class="text-sm text-gray-600 mb-5" aria-label="Breadcrumb">
  <ol class="flex flex-wrap items-center gap-2">
    <?php foreach($breadcrumb as $i=>$bc): ?>
      <li class="flex items-center gap-2">
        <?php if($i < count($breadcrumb)-1): ?>
          <a class="hover:text-indigo-700 hover:underline" href="<?= htmlspecialchars($bc['url']) ?>"><?= htmlspecialchars($bc['name']) ?></a>
          <span aria-hidden="true">›</span>
        <?php else: ?>
          <span class="font-semibold text-gray-800"><?= htmlspecialchars($bc['name']) ?></span>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</nav>
<?php endif; ?>

<!-- HEADER -->
<?php
/* ===============================
STATE PAGE
=============================== */
if($pageType=="state"){
?>

<?php
$stateName=trim((string)$pageData['statename']);
$stateSeed=abs(crc32(mb_strtolower($stateName)));
$stateDistrictRows=[];
$stateDistrictStmt=$conn->prepare("
SELECT district,COUNT(*) total,COUNT(DISTINCT pincode) pincode_total
FROM post_offices
WHERE statename=?
GROUP BY district
ORDER BY total DESC,district ASC
");
if($stateDistrictStmt){
    $stateDistrictStmt->bind_param("s",$stateName);
    $stateDistrictStmt->execute();
    $stateDistrictRes=$stateDistrictStmt->get_result();
    while($stateDistrictRes && $districtRow=$stateDistrictRes->fetch_assoc()){
        $stateDistrictRows[]=$districtRow;
    }
    $stateDistrictStmt->close();
}

$stateDistrictCount=count($stateDistrictRows);
$stateOfficeCount=0;
$statePincodeCount=0;
foreach($stateDistrictRows as $districtRow){
    $stateOfficeCount+=(int)($districtRow['total'] ?? 0);
    $statePincodeCount+=(int)($districtRow['pincode_total'] ?? 0);
}

$popularDistricts=array_slice($stateDistrictRows,0,6);
$popularDistrictNames=array_map(static fn($row)=> (string)($row['district'] ?? ''),$popularDistricts);

$allStateNames=[];
$statePoolRes=$conn->query("SELECT DISTINCT statename FROM post_offices ORDER BY statename");
if($statePoolRes){
    while($poolRow=$statePoolRes->fetch_assoc()){
        $allStateNames[]=(string)($poolRow['statename'] ?? '');
    }
}
$fallbackNearby=[];
foreach($allStateNames as $candidateState){
    if(mb_strtolower(trim($candidateState))===mb_strtolower($stateName)){
        continue;
    }
    $fallbackNearby[]=$candidateState;
}
$nearbyStates=getNearbyStatesFor($stateName,$fallbackNearby);

$topPincodeRows=[];
$topPincodeStmt=$conn->prepare("
SELECT pincode,COUNT(*) office_total,MIN(district) district_name
FROM post_offices
WHERE statename=?
GROUP BY pincode
ORDER BY office_total DESC,pincode ASC
LIMIT 10
");
if($topPincodeStmt){
    $topPincodeStmt->bind_param("s",$stateName);
    $topPincodeStmt->execute();
    $topPincodeRes=$topPincodeStmt->get_result();
    while($topPincodeRes && $pinRow=$topPincodeRes->fetch_assoc()){
        $topPincodeRows[]=$pinRow;
    }
    $topPincodeStmt->close();
}

$metroDistrictExample=pickVariant($popularDistrictNames,$stateSeed,2);
$cultureDistrictExample=pickVariant($popularDistrictNames,$stateSeed,4);
if($metroDistrictExample==='' && !empty($stateDistrictRows)){
    $metroDistrictExample=(string)$stateDistrictRows[0]['district'];
}
if($cultureDistrictExample==='' && !empty($stateDistrictRows)){
    $cultureDistrictExample=(string)$stateDistrictRows[min(1,count($stateDistrictRows)-1)]['district'];
}

$stateParagraphTemplates=[
    'Across %s, PIN code intelligence is now central to address quality, courier planning, document dispatch, and serviceability checks. This state has %d districts represented in the live directory, covering %d mapped post offices and %d unique pincodes. Because large states contain metro clusters, industrial belts, and rural service pockets at the same time, a static paragraph often fails to explain local complexity. This page therefore builds location commentary dynamically from the current district and pincode dataset so users get context that reflects real delivery patterns instead of a common block repeated for every state.',
    '%s postal movement is shaped by district-level demand rather than one single urban center. Some districts handle high ecommerce throughput, some process institutional records, and others see village-focused parcel cycles. The combination of Head Offices, Sub Offices, and Branch Offices determines how quickly letters and consignments move from regional sorting hubs to final delivery beats. When users validate addresses against this dataset, they reduce non-delivery events, improve first-attempt success, and avoid expensive reroutes that usually happen when localities share similar names but different pincodes.',
    'Dynamic variation logic is useful because districts are not equal in operational pressure. For example, %s can behave like a high-volume dispatch corridor during business cycles, while %s may reflect tourism, education, agriculture, or cultural travel demand at different times of the year. These differences influence pickup schedules, hub prioritization, and last-mile beat planning. By generating district-specific statements from live counts, the content stays practical for residents, merchants, call-center teams, and logistics coordinators who need state context before selecting a final office-level record.',
    'A valid Indian address is not just street plus city; it requires the right district and exact six-digit PIN. In %s, this matters for KYC submissions, admission forms, legal notices, pharmacy shipments, government communications, and B2C deliveries. When the wrong pincode is paired with a correct-looking locality, automated sorters may still push the parcel into a different branch chain. This state page helps prevent that mismatch by letting users move from broad state discovery to district drill-down without losing the postal hierarchy that India Post routing depends on.',
    'Teams that operate across multiple districts in %s can use this page as a planning layer. Procurement teams can review district spread, customer support teams can confirm official office names, and operations teams can compare pincode concentration before creating dispatch rules. Instead of hardcoded copy, each paragraph here rotates through controlled templates and data substitutions so the narrative changes by state identity and measured coverage. This approach avoids repetitive SEO blocks while still keeping language clear, factual, and useful for people who need trustworthy postal references.',
    'If you are comparing serviceability in neighboring regions, use the nearby state references and popular district snapshots below. Together they create a realistic operational picture: where density is high, where coverage is geographically wide, and where route planning may need extra validation. The goal is simple—make %s postal data easier to understand at scale, while preserving district-level precision for final address decisions. Continue into district pages to inspect office lists, then open any pincode profile for deeper delivery context and hierarchy details.'
];
$stateWordTarget=1000;
$stateWords=0;
$stateGeneratedParagraphs=[];
$stateLoop=0;
while($stateWords < $stateWordTarget){
    $template=pickVariant($stateParagraphTemplates,$stateSeed,$stateLoop);
    $paragraph=sprintf($template,htmlspecialchars($stateName),(int)$stateDistrictCount,(int)$stateOfficeCount,(int)$statePincodeCount,htmlspecialchars((string)$metroDistrictExample),htmlspecialchars((string)$cultureDistrictExample),htmlspecialchars($stateName),htmlspecialchars($stateName),htmlspecialchars($stateName),htmlspecialchars($stateName));
    $stateGeneratedParagraphs[]=$paragraph;
    $stateWords += str_word_count(strip_tags($paragraph));
    $stateLoop++;
}
?>

<h2 class="text-3xl font-bold mb-8">
<?= strtoupper($stateName); ?> Pincode List
</h2>

<section class="state-intro bg-white rounded-xl shadow p-6 mb-8 leading-7">
<h2 class="text-2xl font-semibold text-indigo-700 mb-4"><?= htmlspecialchars($stateName) ?> PIN Code Directory – Overview</h2>

<?php foreach($stateGeneratedParagraphs as $stateParagraph): ?>
<p class="text-gray-700 mb-4"><?= $stateParagraph ?></p>
<?php endforeach; ?>

<div class="rounded-lg border border-slate-200 bg-slate-50 p-4 mb-4">
  <h3 class="text-xl font-semibold text-indigo-700 mb-3">Popular districts in <?= htmlspecialchars($stateName) ?></h3>
  <ul class="list-disc pl-5 text-gray-700 space-y-1">
    <?php foreach($popularDistricts as $popularDistrict): ?>
      <?php $popularSlug=toSlug((string)$popularDistrict['district']); ?>
      <li>
        <a class="text-indigo-700 underline" href="/<?= htmlspecialchars($popularSlug) ?>-pincode"><?= htmlspecialchars((string)$popularDistrict['district']) ?></a>
        – <?= (int)($popularDistrict['total'] ?? 0) ?> mapped post offices and <?= (int)($popularDistrict['pincode_total'] ?? 0) ?> pincodes in the current directory.
      </li>
    <?php endforeach; ?>
  </ul>
</div>

<div class="rounded-lg border border-slate-200 bg-slate-50 p-4 mb-4">
  <h3 class="text-xl font-semibold text-indigo-700 mb-3">Nearby <?= htmlspecialchars($stateName) ?> states</h3>
  <p class="text-gray-700">
    <?= htmlspecialchars($stateName) ?> shares practical delivery and transport corridors with
    <?= htmlspecialchars(implode(', ', $nearbyStates)); ?>.
  </p>
</div>

<div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
  <h3 class="text-xl font-semibold text-indigo-700 mb-3">Top searched pincodes</h3>
  <ul class="list-disc pl-5 text-gray-700 space-y-1">
    <?php foreach($topPincodeRows as $topPin): ?>
      <li>
        <a class="text-indigo-700 underline" href="/<?= htmlspecialchars((string)$topPin['pincode']) ?>-pincode"><?= htmlspecialchars((string)$topPin['pincode']) ?></a>
        – high reference volume in <?= htmlspecialchars((string)$topPin['district_name']) ?> with <?= (int)($topPin['office_total'] ?? 0) ?> linked post-office entries.
      </li>
    <?php endforeach; ?>
  </ul>
</div>
</section>

<div class="grid md:grid-cols-2 gap-5">

<?php foreach($stateDistrictRows as $row){ ?>
<?php $districtSlug=toSlug((string)$row['district']); ?>
<a class="bg-white p-6 rounded-xl shadow block hover:shadow-md transition"
href="/<?= htmlspecialchars($districtSlug) ?>-pincode">
<h3 class="font-semibold text-2xl mb-2"><?= htmlspecialchars(strtoupper((string)$row['district'])); ?></h3>
<p class="text-gray-700 text-lg"><?= (int)$row['total']; ?> Post Offices</p>
</a>
<?php } ?>

</div>

<?php }
elseif($pageType=="district"){
?>

<?php
$districtName=trim((string)$pageData['district']);
$districtSeed=abs(crc32(mb_strtolower($districtName)));
$districtStateRows=[];
$districtStateStmt=$conn->prepare("
SELECT statename,COUNT(*) office_total,COUNT(DISTINCT pincode) pincode_total
FROM post_offices
WHERE district=?
GROUP BY statename
ORDER BY office_total DESC,statename ASC
");
if($districtStateStmt){
    $districtStateStmt->bind_param("s",$districtName);
    $districtStateStmt->execute();
    $districtStateRes=$districtStateStmt->get_result();
    while($districtStateRes && $stateRow=$districtStateRes->fetch_assoc()){
        $districtStateRows[]=$stateRow;
    }
    $districtStateStmt->close();
}
$primaryStateName=(string)($districtStateRows[0]['statename'] ?? 'India');
$districtOfficeTotal=0;
$districtPincodeTotal=0;
foreach($districtStateRows as $stateSummaryRow){
    $districtOfficeTotal+=(int)($stateSummaryRow['office_total'] ?? 0);
    $districtPincodeTotal+=(int)($stateSummaryRow['pincode_total'] ?? 0);
}

$districtPinHighlights=[];
$districtPinStmt=$conn->prepare("
SELECT pincode,COUNT(*) office_total,MIN(officename) sample_office
FROM post_offices
WHERE district=?
GROUP BY pincode
ORDER BY office_total DESC,pincode ASC
LIMIT 10
");
if($districtPinStmt){
    $districtPinStmt->bind_param("s",$districtName);
    $districtPinStmt->execute();
    $districtPinRes=$districtPinStmt->get_result();
    while($districtPinRes && $pinSummary=$districtPinRes->fetch_assoc()){
        $districtPinHighlights[]=$pinSummary;
    }
    $districtPinStmt->close();
}

$districtParagraphTemplates=[
    '%s district in %s has a layered postal footprint that includes urban delivery pockets, peri-urban growth corridors, and locality-specific branch service points. The current dataset maps %d post-office entries and %d unique pincodes for this district. That breadth is important because users often search a district name first, then need to identify the exact office handling their address. Dynamic district copy solves this by combining real counts with practical postal guidance, helping visitors understand both scale and precision before selecting a pincode-level record.',
    'In district workflows, one repeated content block is rarely accurate for every geography. Some neighborhoods in %s can generate dense shipment traffic, while outlying mandals or talukas rely on fewer but essential branch offices for last-mile delivery. By rotating structured sentence variants, this page avoids repetitive patterning and reflects district-specific behavior more clearly. That means merchants can validate COD zones, institutions can confirm dispatch addresses, and residents can cross-check official office names without jumping between disconnected sources.',
    'The six-digit PIN hierarchy remains the strongest routing signal for %s addresses. Even when street names are accurate, an incorrect pin may send consignments into another sorting chain. District-level review helps catch these errors early. You can scan office names, compare pincode clusters, and identify which segments carry heavier operational loads. This is particularly useful for legal notices, educational documents, healthcare parcels, ecommerce returns, and service engineer visits where timing and address integrity directly affect user experience and cost outcomes.',
    'From a planning perspective, %s district pages support multiple use cases at once: customer support verification, warehouse dispatch mapping, return reduction analysis, and field-operations coordination. Instead of static text, these paragraphs are generated with variation logic using district seed values, coverage totals, and state linkage. The result is long-form but still contextual content that can scale across districts without producing near-duplicate pages. It keeps language human-readable while preserving the factual backbone required for postal lookup tasks.',
    'Users can treat this directory as a district command center. Start with overview context, inspect high-activity pincode pockets, and open office pages when exact branch references are needed. As address systems evolve, this structure stays useful because it is based on dataset-backed relationships between district, state, office, and pin. Whether your goal is to send one parcel or optimize thousands of consignments, a district-first validation flow in %s reduces ambiguity and supports better delivery confidence.'
];
$districtWordTarget=1000;
$districtWords=0;
$districtGeneratedParagraphs=[];
$districtLoop=0;
while($districtWords < $districtWordTarget){
    $template=pickVariant($districtParagraphTemplates,$districtSeed,$districtLoop);
    $paragraph=sprintf($template,htmlspecialchars($districtName),htmlspecialchars($primaryStateName),(int)$districtOfficeTotal,(int)$districtPincodeTotal,htmlspecialchars($districtName),htmlspecialchars($districtName),htmlspecialchars($districtName),htmlspecialchars($districtName),htmlspecialchars($districtName));
    $districtGeneratedParagraphs[]=$paragraph;
    $districtWords += str_word_count(strip_tags($paragraph));
    $districtLoop++;
}

$stmt=$conn->prepare("
SELECT officename,pincode,statename,district
FROM post_offices
WHERE district=?
ORDER BY statename,officename
LIMIT 2000
");
$stmt->bind_param("s",$districtName);
$stmt->execute();
$res=$stmt->get_result();
?>

<h2 class="text-3xl font-bold mb-8">
<?= strtoupper($districtName); ?> District Pincode List
</h2>

<section class="district-intro bg-white rounded-xl shadow p-6 mb-8 leading-7">
  <h2 class="text-2xl font-semibold text-indigo-700 mb-4"><?= htmlspecialchars($districtName) ?> District Dynamic Postal Overview</h2>
  <?php foreach($districtGeneratedParagraphs as $districtParagraph): ?>
    <p class="text-gray-700 mb-4"><?= $districtParagraph ?></p>
  <?php endforeach; ?>

  <?php if(!empty($districtPinHighlights)): ?>
  <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
    <h3 class="text-xl font-semibold text-indigo-700 mb-3">High-activity pincode pockets in <?= htmlspecialchars($districtName) ?></h3>
    <ul class="list-disc pl-5 text-gray-700 space-y-1">
      <?php foreach($districtPinHighlights as $pinHighlight): ?>
      <li>
        <a class="text-indigo-700 underline" href="/<?= htmlspecialchars((string)$pinHighlight['pincode']) ?>-pincode"><?= htmlspecialchars((string)$pinHighlight['pincode']) ?></a>
        – anchored by <?= htmlspecialchars((string)$pinHighlight['sample_office']) ?> and <?= (int)($pinHighlight['office_total'] ?? 0) ?> mapped office records.
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>
</section>

<div class="grid md:grid-cols-2 gap-5">
<?php while($row=$res->fetch_assoc()){
    $officeSlug=toSlug((string)$row['officename']);
?>
<div class="bg-white p-5 rounded-xl shadow w-full">
<h3 class="font-semibold">
<a class="text-indigo-700 hover:underline" href="/<?= htmlspecialchars($officeSlug) ?>-post-office-<?= htmlspecialchars((string)$row['pincode']) ?>">
<?= htmlspecialchars((string)$row['officename']) ?>
</a>
</h3>
<p><?= htmlspecialchars(strtoupper((string)$row['district'])) ?>, <?= htmlspecialchars(strtoupper((string)$row['statename'])) ?></p>
<p>Pincode: <b><?= htmlspecialchars((string)$row['pincode']) ?></b></p>
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
$districtOfficeCount=0;
$districtPincodeCount=0;
$districtSummaryStmt=$conn->prepare("SELECT COUNT(*) office_count, COUNT(DISTINCT pincode) pincode_count FROM post_offices WHERE district=? AND statename=?");

if($districtSummaryStmt){
    $districtSummaryStmt->bind_param("ss",$districtName,$stateName);
    $districtSummaryStmt->execute();
    $districtSummaryRes=$districtSummaryStmt->get_result();
    if($districtSummaryRes && $districtSummaryRow=$districtSummaryRes->fetch_assoc()){
        $districtOfficeCount=(int)($districtSummaryRow['office_count'] ?? 0);
        $districtPincodeCount=(int)($districtSummaryRow['pincode_count'] ?? 0);
    }
    $districtSummaryStmt->close();
}

$districtMapQuery=trim($districtName.", ".$stateName.", India");
$districtMapEmbedUrl="https://maps.google.com/maps?q=".rawurlencode($districtMapQuery)."&output=embed";
$stateSlug = $stateName!=='' ? toSlug($stateName) : '';
$districtSlug = $districtName!=='' ? toSlug($districtName) : '';
$pinLastDigit = (int)substr($pinCode,-1);
$pinVariantIndex = $pinLastDigit % 3;
$nearbyImportanceCopy = [
    "Nearby PIN codes often share sorting paths, transport corridors, and delivery dependencies with <strong>".htmlspecialchars($pinCode)."</strong>. Reviewing adjacent clusters helps you catch locality mismatches before booking a shipment.",
    "Addresses that look similar can still map to different delivery beats. Comparing <strong>".htmlspecialchars($pinCode)."</strong> with neighboring PIN clusters in ".htmlspecialchars($districtName)." helps reduce failed pickups and rerouting delays.",
    "When dispatch teams validate only one PIN code, hidden edge-case errors are easy to miss. A quick scan of nearby postal clusters around <strong>".htmlspecialchars($pinCode)."</strong> creates a more reliable routing decision."
];
$useCaseTitleVariants = [
    "Practical Use Cases for PIN ".htmlspecialchars($pinCode),
    "Where This PIN Code Helps in Daily Workflows",
    "How People Actually Use PIN ".htmlspecialchars($pinCode)
];
$localAreaKnownForVariants = [
    "This pocket of ".htmlspecialchars($districtName)." is widely recognized for its mix of neighborhood markets, daily-need retail activity, and steady residential movement linked to ".htmlspecialchars($officeName)." Post Office.",
    htmlspecialchars($officeName)." and the surrounding stretches of ".htmlspecialchars($districtName)." are known for practical, day-to-day community life where residential zones, small businesses, and routine courier activity run side by side.",
    "The ".htmlspecialchars($pinCode)." belt in ".htmlspecialchars($districtName)." is commonly identified by local trade pockets, service establishments, and consistent household delivery demand around ".htmlspecialchars($officeName)."."
];
$importanceContextVariants = [
    "For residents, it prevents confusion between similarly named neighborhoods. For online sellers and delivery teams, it lowers failed delivery attempts and repeat calls.",
    "For families and students, it helps documents reach the right area without delay. For businesses, it becomes a dependable checkpoint in dispatch and billing systems.",
    "For anyone filling forms, it improves address clarity. For logistics operations, it supports cleaner routing, better SLA performance, and fewer return-to-origin cases."
];
$nearbyPincodes = [];
$nearbyStmt = $conn->prepare("
    SELECT pincode, MIN(officename) AS sample_office, COUNT(*) AS office_count
    FROM post_offices
    WHERE district=? AND statename=? AND pincode<>?
    GROUP BY pincode
    ORDER BY pincode ASC
    LIMIT 8
");

if($nearbyStmt){
    $nearbyStmt->bind_param("sss",$districtName,$stateName,$pinCode);
    $nearbyStmt->execute();
    $nearbyRes=$nearbyStmt->get_result();
    if($nearbyRes){
        while($nearbyRow=$nearbyRes->fetch_assoc()){
            $nearbyPincodes[]=$nearbyRow;
        }
    }
    $nearbyStmt->close();
}

$officeTypeCounts = [];
$deliveryStatusCounts = [];
$sampleOfficeNames = [];

foreach ($pageData as $officeRow) {
    $officeType = trim((string)($officeRow['officetype'] ?? 'Unknown'));
    $deliveryStatus = trim((string)($officeRow['delivery'] ?? 'Unknown'));
    $officeLabel = trim((string)($officeRow['officename'] ?? ''));

    if ($officeType === '') {
        $officeType = 'Unknown';
    }
    if ($deliveryStatus === '') {
        $deliveryStatus = 'Unknown';
    }

    $officeTypeCounts[$officeType] = ($officeTypeCounts[$officeType] ?? 0) + 1;
    $deliveryStatusCounts[$deliveryStatus] = ($deliveryStatusCounts[$deliveryStatus] ?? 0) + 1;

    if ($officeLabel !== '' && count($sampleOfficeNames) < 3) {
        $sampleOfficeNames[] = $officeLabel;
    }
}

$officeTypeSummary = [];
foreach ($officeTypeCounts as $officeType => $count) {
    $officeTypeSummary[] = $officeType . ' (' . $count . ')';
}

$deliverySummary = [];
foreach ($deliveryStatusCounts as $deliveryStatus => $count) {
    $deliverySummary[] = $deliveryStatus . ' (' . $count . ')';
}
?>

<nav class="text-sm mb-4 text-gray-600" aria-label="Pincode page breadcrumb">
  <ol class="flex flex-wrap items-center gap-2">
    <li><a class="hover:text-indigo-700 hover:underline" href="/">Home</a></li>
    <?php if($stateSlug!==''): ?>
      <li><span aria-hidden="true">›</span></li>
      <li><a class="hover:text-indigo-700 hover:underline" href="/<?= htmlspecialchars($stateSlug) ?>-pincode"><?= htmlspecialchars($stateName) ?> pincode directory</a></li>
    <?php endif; ?>
    <?php if($districtSlug!==''): ?>
      <li><span aria-hidden="true">›</span></li>
      <li><a class="hover:text-indigo-700 hover:underline" href="/<?= htmlspecialchars($districtSlug) ?>-pincode"><?= htmlspecialchars($districtName) ?> district pincodes</a></li>
    <?php endif; ?>
    <li><span aria-hidden="true">›</span></li>
    <li class="font-semibold text-gray-800">PIN <?= htmlspecialchars($pinCode) ?> details</li>
  </ol>
</nav>

<h2 class="text-3xl font-bold mb-8">
Pincode <?= htmlspecialchars($pinCode) ?>
</h2>

<button class="mb-8 rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($pinCode, ENT_QUOTES) ?>')">
Copy Pincode
</button>

<section class="bg-white rounded-xl shadow p-6 mb-8">
  <h3 class="text-xl font-semibold mb-3">District Map for <?= htmlspecialchars($districtName) ?>, <?= htmlspecialchars($stateName) ?></h3>
  <div class="rounded-lg overflow-hidden border border-slate-200 mb-4">
    <iframe
      title="District map for <?= htmlspecialchars($districtName) ?>"
      src="<?= htmlspecialchars($districtMapEmbedUrl) ?>"
      width="100%"
      height="320"
      style="border:0;"
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade"></iframe>
  </div>
  <p class="text-gray-700 mb-2">
    PIN code <strong><?= htmlspecialchars($pinCode) ?></strong> falls in <strong><?= htmlspecialchars($districtName) ?></strong> district of <strong><?= htmlspecialchars($stateName) ?></strong>. Use this district-level map to understand the broader service geography around this pincode.
  </p>
  <p class="text-gray-700">
    Postal records currently show <strong><?= htmlspecialchars((string)$districtOfficeCount) ?></strong> post offices and <strong><?= htmlspecialchars((string)$districtPincodeCount) ?></strong> unique pincodes in this district. This context is generated automatically from the mapped district of the searched pincode.
  </p>
</section>

<section class="bg-indigo-50 border border-indigo-100 rounded-xl p-6 mb-8 leading-7">
  <h3 class="text-xl font-semibold mb-3">PIN <?= htmlspecialchars($pinCode) ?> at a glance</h3>
  <p class="text-gray-700 mb-3">
    This PIN currently maps <strong><?= htmlspecialchars((string)count($pageData)) ?></strong> post office<?= count($pageData) === 1 ? '' : 's' ?>
    in <strong><?= htmlspecialchars($districtName) ?></strong>, <strong><?= htmlspecialchars($stateName) ?></strong>.
    Representative offices include <strong><?= htmlspecialchars(implode(', ', $sampleOfficeNames)) ?></strong>.
  </p>
  <p class="text-gray-700 mb-3">
    The office mix under this PIN includes <?= htmlspecialchars(implode(', ', $officeTypeSummary)) ?>,
    while delivery status in the current dataset is recorded as <?= htmlspecialchars(implode(', ', $deliverySummary)) ?>.
  </p>
  <p class="text-gray-700">
    Use this summary to confirm whether the PIN represents a broader delivery cluster, a smaller branch-office pocket,
    or a mixed postal service area before dispatching important parcels, forms, or verification documents.
  </p>
</section>

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
  <p class="mt-3"><a class="text-indigo-700 underline hover:no-underline" href="/nearest-railway-station-<?= urlencode($pinCode) ?>">View full railway connectivity page for PIN <?= htmlspecialchars($pinCode) ?></a></p>
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

<section class="bg-white rounded-xl shadow p-6 mb-8 leading-7 text-gray-800" aria-labelledby="pin-local-guide-heading">
  <header class="mb-4">
    <h2 id="pin-local-guide-heading" class="text-2xl font-semibold mb-2">Local Guide to <?= htmlspecialchars($officeName) ?>, <?= htmlspecialchars($districtName) ?> (<?= htmlspecialchars($pinCode) ?>)</h2>
    <p class="text-gray-700">If you are checking this page for practical address use, this section gives a ground-level view of how this postal area works in everyday life.</p>
  </header>

  <article>
    <h3 class="text-xl font-semibold mb-2">What this pincode area is known for</h3>
    <p class="mb-4">
      <?= $localAreaKnownForVariants[$pinVariantIndex] ?> In most cases, people use this pincode for regular household deliveries, office correspondence, utility paperwork, and ecommerce orders. The locality profile is usually a combination of residential blocks and service-driven establishments, so the same PIN can be used by families, students, working professionals, and local shop owners.
      That is why <?= htmlspecialchars($pinCode) ?> is not just a number on an envelope; it represents a real service jurisdiction around <?= htmlspecialchars($officeName) ?> in <?= htmlspecialchars($districtName) ?>, <?= htmlspecialchars($stateName) ?>.
    </p>

    <h3 class="text-xl font-semibold mb-2">How PIN code <?= htmlspecialchars($pinCode) ?> helps delivery systems</h3>
    <p class="mb-4">
      Courier and postal networks read PIN codes before they read full address lines. Once <?= htmlspecialchars($pinCode) ?> is entered correctly, sorting teams can route shipments to the right district path, then to the correct delivery office linked with <?= htmlspecialchars($officeName) ?>. This reduces manual sorting dependency and speeds up movement between hub, sub-hub, and last-mile delivery points.
      Even when street names are abbreviated or spelled differently, the PIN code provides a stable routing anchor. For this reason, users should always write locality name, district, state, and PIN together rather than depending only on landmark references.
    </p>

    <h3 class="text-xl font-semibold mb-2">Nearby locality context and why it matters</h3>
    <p class="mb-4">
      Postal boundaries do not always match the way people describe neighborhoods in daily conversation. A nearby area that sounds similar may belong to another service beat or even a different PIN cluster. In and around <?= htmlspecialchars($districtName) ?>, adjacent localities can share roads and markets but still be mapped to different delivery offices.
      So if an address sits near a boundary zone, confirming the exact PIN <?= htmlspecialchars($pinCode) ?> with the receiving party can prevent delays, reattempts, and reverse logistics. This is especially useful for medicine deliveries, legal documents, bank cards, admission letters, and other time-sensitive items.
    </p>

    <h3 class="text-xl font-semibold mb-2">Importance of this pincode for users and businesses</h3>
    <p>
      <?= $importanceContextVariants[$pinVariantIndex] ?> For support teams, this improves customer communication because delivery commitments can be made against a verified service area. For individuals, it simplifies form filling across KYC, insurance, education, and government portals where postal accuracy is essential.
      In short, using <?= htmlspecialchars($pinCode) ?> with the correct office, district, and state details helps turn a basic address into a delivery-ready address. That small step improves reliability for both personal and business communication across <?= htmlspecialchars($stateName) ?>.
    </p>
  </article>
</section>

<section class="bg-white rounded-xl shadow p-6 mb-8 leading-7 text-gray-800">
  <h3 class="text-2xl font-semibold mb-4">Delivery Status, Address Use, and Service Notes</h3>
  <p class="mb-4">PIN code <strong><?= htmlspecialchars($pinCode) ?></strong> is commonly used in address verification, shipment booking, KYC forms, and public-service document dispatch. For best delivery outcomes, always write the full address with house or building number, street/locality name, landmark (if any), post office name, district, state, and the exact six-digit PIN.</p>
  <p class="mb-4">The delivery status label associated with a post office (for example, Delivery or Non-Delivery) helps indicate whether routine mail delivery is handled directly by that office or routed through a linked delivery center. Business users shipping invoices, legal notices, and replacement cards should use this PIN exactly as registered in customer records to reduce return-to-origin events and sorting delays.</p>
  <p class="mb-4">For ecommerce and logistics teams, this page gives a practical location context for serviceability checks. Pair the PIN with locality and district details to avoid incorrect city mapping in checkout systems. If two locations have similar names, the PIN code should be treated as the final routing identifier in your order pipeline.</p>
  <p class="mb-0">For government or compliance-sensitive communication, cross-check latest office timings, holidays, and operational notices using official India Post channels before dispatching time-bound documents.</p>
</section>

<section class="bg-white rounded-xl shadow p-6 mb-8">
  <section class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 mb-8">
    <h3 class="text-xl font-semibold mb-3">How This PIN Code Connects Nearby Areas</h3>
    <p class="text-gray-700 leading-7">
      The PIN code <strong><?= htmlspecialchars($pinCode) ?></strong> is part of a larger postal routing network in <?= htmlspecialchars($districtName) ?>.
      Delivery efficiency depends on how nearby PIN clusters interact with sorting hubs and sub-offices.
      
      If you're sending parcels, checking nearby service zones can help avoid delays caused by incorrect routing.
    </p>
  </section>

  <h3 class="text-2xl font-semibold mb-4">Nearby PIN Codes in <?= htmlspecialchars($districtName) ?> District</h3>
  <p class="text-gray-700 mb-4">
    Browse nearby postal areas for stronger address validation and routing checks. These internal links help you compare surrounding service zones in
    <a class="text-indigo-700 underline" href="/<?= htmlspecialchars($districtSlug) ?>-pincode"><?= htmlspecialchars($districtName) ?> district</a>
    and
    <a class="text-indigo-700 underline" href="/<?= htmlspecialchars($stateSlug) ?>-pincode"><?= htmlspecialchars($stateName) ?> state</a>.
  </p>
  <div class="mb-5 rounded-lg border border-slate-200 bg-slate-50 p-4">
    <h4 class="text-lg font-semibold text-slate-800 mb-2">Why nearby PIN codes matter</h4>
    <p class="text-gray-700 leading-7"><?= $nearbyImportanceCopy[$pinVariantIndex] ?></p>
  </div>
  <?php if(!empty($nearbyPincodes)): ?>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
      <?php foreach($nearbyPincodes as $nearby): ?>
        <?php
          $nearbyPin = (string)($nearby['pincode'] ?? '');
          $nearbySampleOffice = trim((string)($nearby['sample_office'] ?? 'Post Office'));
          $nearbyOfficeCount = (int)($nearby['office_count'] ?? 0);
          $nearbyAnchor = [
            "Explore PIN ".$nearbyPin." service profile",
            "Check locality coverage for ".$nearbyPin,
            "View postal offices under ".$nearbyPin
          ][((int)substr($nearbyPin,-1)) % 3];
        ?>
        <a class="border border-slate-200 rounded-lg p-3 hover:bg-slate-50 block" href="/<?= htmlspecialchars((string)$nearby['pincode']) ?>-pincode" title="<?= htmlspecialchars((string)$nearby['pincode']) ?> PIN code in <?= htmlspecialchars($districtName) ?>, <?= htmlspecialchars($stateName) ?>">
          <div class="font-semibold text-indigo-700">
            <?= htmlspecialchars($nearbyAnchor) ?>
          </div>
          <div class="text-sm text-gray-600">
            Starts from <?= htmlspecialchars($nearbySampleOffice) ?> in <?= htmlspecialchars($districtName) ?>, <?= htmlspecialchars($stateName) ?>.
          </div>
          <div class="text-xs text-gray-500 mt-1">
            <?= htmlspecialchars((string)$nearbyOfficeCount) ?> mapped post offices • Useful for routing comparison and fallback dispatch planning.
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="text-gray-700 mb-5">Nearby PIN clusters are not available in the current dataset snapshot for this district.</p>
  <?php endif; ?>

  <h4 class="text-xl font-semibold mb-2">Frequently Asked Questions</h4>
  <div class="space-y-3 text-gray-700 leading-7">
    <p><strong>1) Why is this PIN code important for courier and Speed Post?</strong><br>Carriers sort by PIN hierarchy first. A correct PIN drastically improves dispatch accuracy and reduces missorting.</p>
    <p><strong>2) Can one PIN code serve multiple localities?</strong><br>Yes. One delivery office can cover multiple neighborhoods or villages under the same PIN jurisdiction.</p>
    <p><strong>3) Should I use district name or city name in address lines?</strong><br>Use both whenever possible. Keep the PIN code exact, because it is the strongest routing signal in postal workflows.</p>
    <p><strong>4) How often can postal mappings change?</strong><br>Operational boundaries and office handling can change over time. For critical deliveries, verify with official India Post information before booking.</p>
    <p><strong>5) Where can I continue browsing this location hierarchy?</strong><br>
      Explore
      <?php if($districtSlug!==''): ?><a class="text-indigo-700 underline" href="/<?= htmlspecialchars($districtSlug) ?>-pincode"><?= htmlspecialchars($districtName) ?> district pages</a><?php endif; ?>
      <?php if($districtSlug!=='' && $stateSlug!==''): ?> and <?php endif; ?>
      <?php if($stateSlug!==''): ?><a class="text-indigo-700 underline" href="/<?= htmlspecialchars($stateSlug) ?>-pincode"><?= htmlspecialchars($stateName) ?> state pages</a><?php endif; ?>,
      or read <a class="text-indigo-700 underline" href="/blog">postal guides</a>.</p>
  </div>

  <?php
  $pincodeFaqSchema = [
      '@context' => 'https://schema.org',
      '@type' => 'FAQPage',
      'mainEntity' => [
          [
              '@type' => 'Question',
              'name' => 'Why is this PIN code important for courier and Speed Post?',
              'acceptedAnswer' => [
                  '@type' => 'Answer',
                  'text' => "For {$pinCode} in {$districtName}, {$stateName}, carriers sort by PIN hierarchy first. A correct PIN drastically improves dispatch accuracy and reduces missorting."
              ]
          ],
          [
              '@type' => 'Question',
              'name' => 'Can one PIN code serve multiple localities?',
              'acceptedAnswer' => [
                  '@type' => 'Answer',
                  'text' => "Yes. PIN {$pinCode} in {$districtName}, {$stateName} can cover multiple neighborhoods or villages under the same postal jurisdiction."
              ]
          ],
          [
              '@type' => 'Question',
              'name' => 'Should I use district name or city name in address lines?',
              'acceptedAnswer' => [
                  '@type' => 'Answer',
                  'text' => "Use both whenever possible for addresses in {$districtName}, {$stateName}. Keep PIN {$pinCode} exact because it is the strongest routing signal in postal workflows."
              ]
          ],
          [
              '@type' => 'Question',
              'name' => 'How often can postal mappings change?',
              'acceptedAnswer' => [
                  '@type' => 'Answer',
                  'text' => "Operational boundaries and office handling can change over time in {$districtName}, {$stateName}. For critical deliveries under PIN {$pinCode}, verify with official India Post information before booking."
              ]
          ],
          [
              '@type' => 'Question',
              'name' => 'Where can I continue browsing this location hierarchy?',
              'acceptedAnswer' => [
                  '@type' => 'Answer',
                  'text' => "Continue from PIN {$pinCode} to district-level and state-level pages for {$districtName}, {$stateName}, or read postal guides for deeper address and routing context."
              ]
          ],
      ],
  ];
  ?>
  <script type="application/ld+json">
<?= json_encode($pincodeFaqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>
</section>

<section class="bg-white rounded-xl shadow p-6 mb-8">
  <h3 class="text-xl font-semibold mb-4"><?= $useCaseTitleVariants[$pinVariantIndex] ?></h3>

  <ul class="list-disc pl-5 text-gray-700 space-y-2">
    <li>Before checkout or parcel booking, review the <a class="text-indigo-700 underline" href="/<?= htmlspecialchars($pinCode) ?>-pincode">complete delivery profile for PIN <?= htmlspecialchars($pinCode) ?></a> to reduce address mismatch risk.</li>

    <?php if($districtSlug!==''): ?>
    <li>For bulk orders, compare serviceability across the <a class="text-indigo-700 underline" href="/<?= htmlspecialchars($districtSlug) ?>-pincode"><?= htmlspecialchars($districtName) ?> district postal index</a> before assigning courier lanes.</li>
    <?php endif; ?>

    <?php if($stateSlug!==''): ?>
    <li>Need inter-district planning? Use the <a class="text-indigo-700 underline" href="/<?= htmlspecialchars($stateSlug) ?>-pincode"><?= htmlspecialchars($stateName) ?> state pincode explorer</a> to validate expansion routes.</li>
    <?php endif; ?>

    <li>For team training, share our <a class="text-indigo-700 underline" href="/blog">postal workflow guides and addressing checklists</a> so customer support can verify pins faster.</li>
  </ul>
</section>

<section class="bg-white rounded-xl shadow p-6 mb-8">
  <h3 class="text-2xl font-semibold mb-4">Explore More</h3>
  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
    <a class="border border-slate-200 rounded-lg p-3 hover:bg-slate-50 block" href="/<?= htmlspecialchars($pinCode) ?>-pincode">
      <div class="font-semibold text-indigo-700">PIN <?= htmlspecialchars($pinCode) ?> Full Overview</div>
      <div class="text-sm text-gray-600">Complete post office list, delivery notes, and address usage guidance.</div>
    </a>
    <?php if($districtSlug!==''): ?>
    <a class="border border-slate-200 rounded-lg p-3 hover:bg-slate-50 block" href="/<?= htmlspecialchars($districtSlug) ?>-pincode">
      <div class="font-semibold text-indigo-700"><?= htmlspecialchars($districtName) ?> District PIN Codes</div>
      <div class="text-sm text-gray-600">Explore all district post offices and linked PIN directories.</div>
    </a>
    <?php endif; ?>
    <?php if($stateSlug!==''): ?>
    <a class="border border-slate-200 rounded-lg p-3 hover:bg-slate-50 block" href="/<?= htmlspecialchars($stateSlug) ?>-pincode">
      <div class="font-semibold text-indigo-700"><?= htmlspecialchars($stateName) ?> State PIN Code Directory</div>
      <div class="text-sm text-gray-600">Navigate state-level district links and postal hierarchy pages.</div>
    </a>
    <?php endif; ?>
    <a class="border border-slate-200 rounded-lg p-3 hover:bg-slate-50 block" href="/blog">
      <div class="font-semibold text-indigo-700">Postal Knowledge Guides</div>
      <div class="text-sm text-gray-600">Read practical articles on routing, addressing, and delivery best practices.</div>
    </a>
  </div>
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
RAILWAY PIN PAGE
=============================== */
elseif($pageType=="railway_pin"){
?>

<?php
$railPin = trim((string)($pageData['pincode'] ?? ''));
$railOffice = trim((string)($pageData['officename'] ?? ''));
$railDistrict = trim((string)($pageData['district'] ?? ''));
$railState = trim((string)($pageData['statename'] ?? ''));
$railStation = trim((string)($pageData['station_name'] ?? ''));
$railCode = trim((string)($pageData['station_code'] ?? ''));
$railDistance = number_format((float)($pageData['distance_km'] ?? 0), 1);
$districtSlug = toSlug($railDistrict);
$stateSlug = toSlug($railState);
?>

<h2 class="text-3xl font-bold mb-4">Nearest Railway Station for PIN Code <?= htmlspecialchars($railPin) ?></h2>
<p class="text-gray-700 mb-6">Programmatically generated local page with postal + rail context for delivery planning, travel convenience, and location intelligence workflows.</p>

<div class="grid md:grid-cols-2 gap-5 mb-8">
  <div class="bg-white p-5 rounded-xl shadow">
    <h3 class="text-xl font-semibold mb-3 text-indigo-900">Railway Connectivity Snapshot</h3>
    <p><b>Nearest Railway Station:</b> <?= htmlspecialchars($railStation) ?></p>
    <p><b>Station Code:</b> <?= htmlspecialchars($railCode) ?></p>
    <p><b>Approx Distance:</b> <?= htmlspecialchars($railDistance) ?> km</p>
    <p><b>Mapped PIN Code:</b> <?= htmlspecialchars($railPin) ?></p>
  </div>
  <div class="bg-white p-5 rounded-xl shadow">
    <h3 class="text-xl font-semibold mb-3 text-indigo-900">Location Coverage</h3>
    <p><b>Post Office:</b> <?= htmlspecialchars($railOffice) ?></p>
    <p><b>District:</b> <?= htmlspecialchars($railDistrict) ?></p>
    <p><b>State:</b> <?= htmlspecialchars($railState) ?></p>
    <p><a class="text-indigo-700 underline" href="/<?= urlencode($railPin) ?>">Open full PIN code page</a></p>
  </div>
</div>

<section class="bg-white rounded-xl shadow p-6 mb-8 leading-7 text-gray-800">
  <h3 class="text-2xl font-semibold mb-3">About this PIN code railway page</h3>
  <p class="mb-3">For PIN code <strong><?= htmlspecialchars($railPin) ?></strong>, the nearest mapped railway station is <strong><?= htmlspecialchars($railStation) ?></strong> (<?= htmlspecialchars($railCode) ?>), roughly <strong><?= htmlspecialchars($railDistance) ?> km</strong> away. This helps users validate local transport proximity while searching post office records.</p>
  <p class="mb-3">These pages are generated from structured datasets and can scale across all mapped PIN codes. The format is intentionally policy-safe for ad-supported publishing: factual, navigation-friendly, and useful for address confirmation journeys.</p>
  <p>Use this as a discovery page, then confirm exact travel, booking, and parcel timelines from official Indian Railways and India Post channels before making decisions.</p>
</section>

<section class="bg-white rounded-xl shadow p-6 mb-8">
  <h3 class="text-2xl font-semibold mb-4">Related internal pages</h3>
  <ul class="list-disc pl-5 space-y-2 text-indigo-700">
    <li><a class="underline" href="/<?= urlencode($railPin) ?>">PIN code <?= htmlspecialchars($railPin) ?> post office directory</a></li>
    <?php if($districtSlug !== ''): ?><li><a class="underline" href="/<?= htmlspecialchars($districtSlug) ?>-pincode"><?= htmlspecialchars($railDistrict) ?> district PIN listings</a></li><?php endif; ?>
    <?php if($stateSlug !== ''): ?><li><a class="underline" href="/<?= htmlspecialchars($stateSlug) ?>-pincode"><?= htmlspecialchars($railState) ?> state PIN listings</a></li><?php endif; ?>
    <li><a class="underline" href="/blog">Postal guides and delivery resources</a></li>
  </ul>
</section>

<?php }

/* ===============================
POST OFFICE PAGE
=============================== */
elseif($pageType=="office"){
?>

<?php
$officeName = trim((string)($pageData['officename'] ?? ''));
$officePin = trim((string)($pageData['pincode'] ?? ''));
$officeDistrict = trim((string)($pageData['district'] ?? ''));
$officeState = trim((string)($pageData['statename'] ?? ''));
$officeType = trim((string)($pageData['officetype'] ?? ''));
$officeDelivery = trim((string)($pageData['delivery'] ?? ''));
$districtSlug = $officeDistrict!=='' ? toSlug($officeDistrict) : '';
$stateSlug = $officeState!=='' ? toSlug($officeState) : '';
$officeDivision = trim((string)($pageData['divisionname'] ?? ''));
$officeRegion = trim((string)($pageData['regionname'] ?? ''));
$officeCircle = trim((string)($pageData['circlename'] ?? ''));
$officeTaluk = trim((string)($pageData['taluk'] ?? ''));

$relatedOffices = [];
$relatedStmt = $conn->prepare("
    SELECT officename,pincode,officetype,delivery,district,statename
    FROM post_offices
    WHERE pincode=? AND officename<>?
    ORDER BY officename ASC
    LIMIT 8
");
if($relatedStmt){
    $relatedStmt->bind_param("ss",$officePin,$officeName);
    $relatedStmt->execute();
    $relatedRes = $relatedStmt->get_result();
    while($relatedRes && $rel = $relatedRes->fetch_assoc()){
        $relatedOffices[]=$rel;
    }
}
$relatedCount = count($relatedOffices);
?>

<h1 class="text-3xl font-bold mb-8">
<?= htmlspecialchars($officeName) ?> Post Office - <?= htmlspecialchars($officePin) ?>
</h1>

<div class="bg-white p-6 rounded-xl shadow space-y-2">
<p><b>Office Name:</b> <?= htmlspecialchars($officeName) ?></p>
<p><b>Pincode:</b> <?= htmlspecialchars($officePin) ?></p>
<p><b>District:</b> <?= htmlspecialchars($officeDistrict) ?></p>
<p><b>State:</b> <?= htmlspecialchars($officeState) ?></p>
<p><b>Office Type:</b> <?= htmlspecialchars($officeType) ?></p>
<p><b>Delivery Status:</b> <?= htmlspecialchars($officeDelivery) ?></p>
</div>

<section class="bg-white p-6 rounded-xl shadow mt-6 leading-7 text-gray-800">
  <h2 class="text-2xl font-semibold mb-3">Local postal context for <?= htmlspecialchars($officeName) ?></h2>
  <p class="mb-3">
    <?= htmlspecialchars($officeName) ?> is mapped under PIN code <strong><?= htmlspecialchars($officePin) ?></strong> in
    <strong><?= htmlspecialchars($officeDistrict) ?></strong> district, <strong><?= htmlspecialchars($officeState) ?></strong>.
    This page is designed to give a clear identity for this specific post office record so users can validate address components before dispatch.
  </p>
  <p class="mb-3">
    Office type is listed as <strong><?= htmlspecialchars($officeType!=='' ? $officeType : 'Not specified') ?></strong> and
    delivery status is <strong><?= htmlspecialchars($officeDelivery!=='' ? $officeDelivery : 'Not specified') ?></strong>.
    For important shipments, always cross-check latest counter timings and service availability from official India Post channels.
  </p>
  <p>
    Nearby hierarchy links: <?php if($districtSlug!==''): ?><a class="text-indigo-700 underline" href="/<?= htmlspecialchars($districtSlug) ?>-pincode"><?= htmlspecialchars($officeDistrict) ?> district PIN directory</a><?php endif; ?><?php if($districtSlug!=='' && $stateSlug!==''): ?> · <?php endif; ?><?php if($stateSlug!==''): ?><a class="text-indigo-700 underline" href="/<?= htmlspecialchars($stateSlug) ?>-pincode"><?= htmlspecialchars($officeState) ?> state PIN directory</a><?php endif; ?>.
  </p>
</section>

<section class="bg-indigo-50 border border-indigo-100 p-6 rounded-xl shadow mt-6 leading-7 text-gray-800">
  <h2 class="text-2xl font-semibold mb-3">Detailed delivery profile and address guidance</h2>
  <p class="mb-3">
    <strong><?= htmlspecialchars($officeName) ?> Post Office</strong> serves PIN code <strong><?= htmlspecialchars($officePin) ?></strong> in
    <strong><?= htmlspecialchars($officeDistrict) ?></strong>, <?= htmlspecialchars($officeState) ?>. For address formatting, include the house/locality name,
    village or urban sector, <strong><?= htmlspecialchars($officeName) ?></strong> as the post office, district, state, and PIN in the final line.
    This reduces sorting delays in automated and manual mail routing workflows.
  </p>
  <p class="mb-3">
    Operationally, this office is tagged as <strong><?= htmlspecialchars($officeType!=='' ? $officeType : 'Not specified') ?></strong>
    with delivery status <strong><?= htmlspecialchars($officeDelivery!=='' ? $officeDelivery : 'Not specified') ?></strong>.
    <?= $officeTaluk!=='' ? 'It is mapped to taluk/tehsil: <strong>'.htmlspecialchars($officeTaluk).'</strong>.' : 'Taluk/tehsil details are not available in the current source record.' ?>
    <?= $officeDivision!=='' ? 'Postal division: <strong>'.htmlspecialchars($officeDivision).'</strong>.' : '' ?>
    <?= $officeRegion!=='' ? 'Region: <strong>'.htmlspecialchars($officeRegion).'</strong>.' : '' ?>
    <?= $officeCircle!=='' ? 'Circle: <strong>'.htmlspecialchars($officeCircle).'</strong>.' : '' ?>
  </p>
  <p>
    Practical use cases include ecommerce order validation, KYC address checks, beneficiary communication, and routing planning for important documents.
    Always verify service windows, pickup availability, and holiday operations directly with official India Post channels before dispatching time-sensitive shipments.
  </p>
</section>

<?php if($relatedCount>0): ?>
<section class="bg-white p-6 rounded-xl shadow mt-6">
  <h2 class="text-2xl font-semibold mb-4">Other post offices under PIN <?= htmlspecialchars($officePin) ?></h2>
  <p class="text-gray-700 mb-3">Found <?= htmlspecialchars((string)$relatedCount) ?> related offices in the same PIN cluster for hierarchy and routing context.</p>
  <div class="grid md:grid-cols-2 gap-3">
    <?php foreach($relatedOffices as $rel): ?>
      <?php $relSlug = toSlug($rel['officename'] ?? ''); ?>
      <?php if($relSlug==='') continue; ?>
      <a class="block border rounded-lg p-3 hover:bg-indigo-50" href="/<?= htmlspecialchars($relSlug) ?>-post-office-<?= htmlspecialchars($rel['pincode']) ?>">
        <div class="font-semibold text-indigo-800"><?= htmlspecialchars($rel['officename']) ?> (<?= htmlspecialchars($rel['pincode']) ?>)</div>
        <div class="text-sm text-gray-700"><?= htmlspecialchars($rel['district']) ?>, <?= htmlspecialchars($rel['statename']) ?></div>
        <div class="text-xs text-gray-600">Type: <?= htmlspecialchars($rel['officetype'] ?? 'N/A') ?> · Delivery: <?= htmlspecialchars($rel['delivery'] ?? 'N/A') ?></div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="bg-white p-6 rounded-xl shadow mt-6">
  <h2 class="text-2xl font-semibold mb-4">FAQs for <?= htmlspecialchars($officeName) ?> Post Office</h2>
  <div class="space-y-4 text-gray-800">
    <div>
      <h3 class="font-semibold">Which district and state does this office belong to?</h3>
      <p><?= htmlspecialchars($officeName) ?> Post Office is listed in <?= htmlspecialchars($officeDistrict) ?> district, <?= htmlspecialchars($officeState) ?>.</p>
    </div>
    <div>
      <h3 class="font-semibold">What is the PIN code for this office?</h3>
      <p>The mapped PIN code for <?= htmlspecialchars($officeName) ?> Post Office is <strong><?= htmlspecialchars($officePin) ?></strong>.</p>
    </div>
    <div>
      <h3 class="font-semibold">Can I use this page for official confirmation?</h3>
      <p>This page is a structured reference. For mission-critical work, confirm final serviceability and timings on official India Post channels.</p>
    </div>
  </div>
</section>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Which district and state does this office belong to?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "<?= htmlspecialchars($officeName) ?> Post Office is listed in <?= htmlspecialchars($officeDistrict) ?> district, <?= htmlspecialchars($officeState) ?>."
      }
    },
    {
      "@type": "Question",
      "name": "What is the PIN code for this office?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "The mapped PIN code for <?= htmlspecialchars($officeName) ?> Post Office is <?= htmlspecialchars($officePin) ?>."
      }
    },
    {
      "@type": "Question",
      "name": "Can I use this page for official confirmation?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "This page is a structured reference. For mission-critical work, confirm final serviceability and timings on official India Post channels."
      }
    }
  ]
}
</script>

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
      <h2 class="text-4xl leading-tight font-extrabold mb-4 text-slate-800">India Pincode Locator</h2>
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

<?php if(!$dbAvailable): ?>
<section class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
  <h2 class="text-lg font-semibold text-amber-900 mb-1">Search is temporarily unavailable</h2>
  <p class="text-amber-800">We are currently unable to connect to the postal database. You can still browse guides, policies, and informational pages while we restore search.</p>
</section>
<?php endif; ?>

<?php if($pageType=="home"): ?>
<script>

const resultsDiv=document.getElementById("results");
const stateSelect=document.getElementById("stateSelect");
const districtSelect=document.getElementById("districtSelect");
const officeSelect=document.getElementById("officeSelect");
const detectNearbyBtn=document.getElementById("detectNearbyBtn");
const nearbyStatus=document.getElementById("nearbyStatus");
const nearbyPincodes=document.getElementById("nearbyPincodes");

resultsDiv.addEventListener("click",async function(event){
const copyBtn=event.target.closest(".js-copy-pin");
if(!copyBtn){
return;
}

const pin=copyBtn.getAttribute("data-pincode") || "";
if(!/^\d{6}$/.test(pin)){
return;
}

const originalText=copyBtn.textContent;

try{
await navigator.clipboard.writeText(pin);
copyBtn.textContent="COPIED";
setTimeout(()=>{
copyBtn.textContent=originalText;
},1500);
}
catch(_error){
copyBtn.textContent="FAILED";
setTimeout(()=>{
copyBtn.textContent=originalText;
},1500);
}
});

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

const railPinInput=document.getElementById("railPinInput");
const railPinButton=document.getElementById("railPinButton");
const railPinStatus=document.getElementById("railPinStatus");

function openRailwayPageForPin(){
const pin=(railPinInput?.value || "").trim();
if(!/^\d{6}$/.test(pin)){
if(railPinStatus){
railPinStatus.textContent="Please enter a valid 6-digit PIN code.";
}
return;
}
window.location.href=`/nearest-railway-station-${encodeURIComponent(pin)}`;
}

if(railPinButton){
railPinButton.addEventListener("click",openRailwayPageForPin);
}

if(railPinInput){
railPinInput.addEventListener("keydown",(event)=>{
if(event.key==="Enter"){
openRailwayPageForPin();
}
});
}

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

data.forEach(row=>{

let map="";

if(row.latitude && row.longitude){
map=`<a target="_blank"
class="text-indigo-600 text-sm mt-2 inline-block"
href="https://www.google.com/maps?q=${row.latitude},${row.longitude}">
📍 View Map</a>`;
}

const pincodeValue = row.pincode ?? row.Pincode ?? "";
const officeName = row.officename || "";
const officeType = row.officetype || "";
const formattedOfficeName = officeType
? `${officeName} (${officeType})`
: officeName;
const divisionName = row.divisionname || "";
const introSentence = pincodeValue
? `The PIN code <strong>${escapeHtml(pincodeValue)}</strong> belongs to <strong>${escapeHtml(formattedOfficeName)}</strong>, located in the ${escapeHtml(row.district || "")} district of ${escapeHtml(row.statename || "")}.`
: "";
const divisionSentence = divisionName
? ` This post office falls under the ${escapeHtml(divisionName)} postal division and serves one of the key localities in the region.`
: "";
const pincodeDetailsLink = pincodeValue
? `<a target="_blank"
class="text-indigo-600 text-sm mt-2 inline-block"
href="https://pincodelocator.co.in/${encodeURIComponent(pincodeValue)}">Click To know more about this ${escapeHtml(pincodeValue)}</a>`
: "";
const copyButton = pincodeValue
? `<button type="button" class="js-copy-pin mt-2 ml-2 inline-block rounded bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-700" data-pincode="${escapeHtml(pincodeValue)}">COPY PINCODE</button>`
: "";

html+=`
<div class="bg-white p-5 rounded-xl shadow w-full">
<p>${introSentence}${divisionSentence}</p>
${pincodeDetailsLink}${copyButton}
${map}
</div>`;
});

if(nearestName && nearestCode){
html+=`
<div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
  <h3 class="font-semibold text-indigo-900">Nearest Railway Station</h3>
  <p class="text-sm text-gray-800 mt-1">Station: <b>${escapeHtml(nearestName)}</b> (${escapeHtml(nearestCode)})</p>
  <p class="text-sm text-gray-800">Approx Distance: <b>${escapeHtml(nearestDistance)}</b> km</p>
</div>`;
}

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
