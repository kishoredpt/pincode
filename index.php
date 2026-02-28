<?php
require_once "config/db.php";

$route = $_GET['route'] ?? '';
$pageType="home";
$pageData=[];

/* =========================
ROUTE ENGINE
========================= */

if($route && preg_match('/^(.+)-post-office-(\d{6})$/',$route,$officeMatch)){

    $officeSlug=$officeMatch[1];
    $pincode=$officeMatch[2];
    $officeName=str_replace('-',' ',$officeSlug);

    $stmt=$conn->prepare("\n        SELECT *\n        FROM post_offices\n        WHERE pincode=? AND LOWER(officename)=LOWER(?)\n        LIMIT 1\n    ");
    $stmt->bind_param("ss",$pincode,$officeName);
    $stmt->execute();
    $res=$stmt->get_result();

    if($res->num_rows>0){
        $pageType="office";
        $pageData=$res->fetch_assoc();
    }
}
elseif($route && str_contains($route,'-pincode')){

    $slug=str_replace('-pincode','',$route);
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
require_once "config/db.php";

/* =========================
   SEO META ENGINE (SAFE)
========================= */

$route = $_GET['route'] ?? '';


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

/* STATE PAGE */
if($pageType=="office"){

    $seoTitle = $pageData['officename']." Post Office (".$pageData['pincode'].") | "
        .$pageData['district'].", ".$pageData['statename'];
    $seoDescription = "Postal details for ".$pageData['officename']
        ." Post Office, pincode ".$pageData['pincode']
        ." in ".$pageData['district'].", ".$pageData['statename'].".";
    $canonical = "https://pincodelocator.co.in/".$route;
}
elseif($route && str_contains($route,'-pincode')){

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

<title><?= $seoTitle ?></title>

<meta name="description" content="<?= $seoDescription ?>">

<link rel="canonical" href="<?= $canonical ?>">

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

<meta name="robots" content="index, follow">

<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-900">

<div class="max-w-[1100px] mx-auto px-4 py-10">

<!-- TOP BAR -->
<div class="flex justify-between items-center mb-6">
<h1 class="text-xl font-bold">📮 PincodeLocator.co.in</h1>

<nav class="space-x-5 text-sm font-medium">
<a href="/">Home</a>
<a href="/about.php">About</a>
<a href="/contact.php">Contact</a>
<a href="/privacy-policy.php">Privacy</a>
<a href="/terms.php">Terms</a>
<a href="/disclaimer.php">Disclaimer</a>
<a href="/editorial-policy.php">Editorial</a>
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

<div class="space-y-4">

<?php while($row=$res->fetch_assoc()){ ?>
<details class="bg-white p-6 rounded-xl shadow">
<summary class="cursor-pointer font-semibold text-lg">
<?= strtoupper($row['district']); ?>
<span class="text-sm text-gray-600">(<?= $row['total']; ?> Post Offices)</span>
</summary>

<?php
$stmtOffice=$conn->prepare("\nSELECT officename,pincode\nFROM post_offices\nWHERE statename=? AND district=?\nORDER BY officename\nLIMIT 500\n");
$stmtOffice->bind_param("ss",$pageData['statename'],$row['district']);
$stmtOffice->execute();
$officeRes=$stmtOffice->get_result();
?>

<ul class="mt-4 grid md:grid-cols-2 gap-2 text-sm">
<?php while($office=$officeRes->fetch_assoc()){
    $officeSlug=strtolower(str_replace(' ','-',$office['officename']));
?>
<li>
<a class="text-indigo-700 hover:underline"
href="/<?= $officeSlug ?>-post-office-<?= $office['pincode'] ?>">
<?= htmlspecialchars($office['officename']) ?> - <?= $office['pincode'] ?>
</a>
</li>
<?php } ?>
</ul>
</details>
<?php } ?>

</div>

<?php }

/* ===============================
PINCODE PAGE
=============================== */
elseif($pageType=="pincode"){
?>

<h2 class="text-3xl font-bold mb-8">
Pincode <?= $pageData[0]['pincode']; ?>
</h2>

<div class="grid md:grid-cols-2 gap-5">

<?php foreach($pageData as $row){ ?>

<div class="bg-white p-6 rounded-xl shadow">

<h3 class="font-semibold">
<?= $row['officename']; ?>
</h3>

<p>
<?= strtoupper($row['district']); ?>,
<?= strtoupper($row['statename']); ?>
</p>

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
<div class="text-center mb-10">

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

<h1 class="text-4xl font-bold text-indigo-700">
India Pincode Locator
</h1>

<p class="mt-2 text-lg font-medium text-gray-700">
Search 1.5+ Lakh Post Offices Across India
</p>

<p class="mt-3 text-gray-600">
Search Indian Post Office details using Pincode or Location.
</p>
</div>

<!-- =========================
ADSENSE GAP 1 (Header Ad)
========================= -->
<div class="bg-white rounded-xl shadow p-6 text-center mb-10">
<!-- Adsense Auto Ad / Banner -->
<span class="text-gray-400 text-sm">
Advertisement Space
</span>
</div>

<!-- SEARCH -->
<div class="bg-white shadow-xl rounded-2xl p-8">

<h2 class="text-xl font-semibold mb-3 text-indigo-600">
🔎 Search by Pincode
</h2>

<input
type="text"
id="pincodeInput"
maxlength="6"
placeholder="Enter 6-digit Pincode"
class="w-full border-2 border-indigo-400 p-4 rounded-lg mb-8 outline-none"
/>

<h2 class="text-xl font-semibold mb-4 text-indigo-600">
📍 Search by Location
</h2>

<div class="grid md:grid-cols-3 gap-4">

<select id="stateSelect" class="p-3 border rounded-lg">
<option value="">Select State</option>
</select>

<select id="districtSelect" class="p-3 border rounded-lg">
<option value="">Select District</option>
</select>

<select id="officeSelect" class="p-3 border rounded-lg">
<option value="">Select Post Office</option>
</select>

</div>
</div>

<!-- RESULTS -->
<div id="results" class="mt-10"></div>
<!-- =========================
ADSENSE GAP 2 (Content Ad)
========================= -->
<div class="bg-white rounded-xl shadow p-6 text-center mt-10">
<span class="text-gray-400 text-sm">
Advertisement Space
</span>
</div>
<!-- CONTENT -->
<div class="bg-white mt-12 p-8 rounded-xl shadow">
<h2 class="text-2xl font-bold mb-4">About India Pincode System</h2>

<p class="text-gray-700 leading-7">
India Post introduced the Postal Index Number system to simplify mail delivery.
Each 6-digit PIN represents a delivery post office, district and state.
</p>
</div>

<!-- STATE AUTHORITY -->
<div class="bg-white mt-12 p-8 rounded-xl shadow">

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

<!-- =========================
ADSENSE GAP 3 (Bottom Ad)
========================= -->
<div class="bg-white rounded-xl shadow p-6 text-center mt-12">
<span class="text-gray-400 text-sm">
Advertisement Space
</span>
</div>

<div class="bg-white mt-12 p-8 rounded-xl shadow">
<h2 class="text-2xl font-bold mb-4">Helpful Resources</h2>
<ul class="list-disc pl-6 text-gray-700 leading-8">
<li><a class="text-indigo-700 hover:underline" href="/about.php">About India Pincode Locator</a></li>
<li><a class="text-indigo-700 hover:underline" href="/editorial-policy.php">Editorial Policy</a></li>
<li><a class="text-indigo-700 hover:underline" href="/content-guidelines.php">Content Guidelines</a></li>
<li><a class="text-indigo-700 hover:underline" href="/privacy-policy.php">Privacy Policy</a></li>
<li><a class="text-indigo-700 hover:underline" href="/blog.php">Postal Guides & Articles</a></li>
<li><a class="text-indigo-700 hover:underline" href="/sitemap_index.php">XML Sitemap Index</a></li>
</ul>
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

html+=`
<div class="bg-white p-6 rounded-xl shadow">
<h3 class="font-semibold text-lg">${row.officename}</h3>
<p>${row.district}, ${row.statename}</p>
<p>Pincode: <b>${row.pincode}</b></p>
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
class="w-full text-left p-4 font-semibold bg-gray-50"
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
<button class="w-full text-left p-3 bg-gray-100"
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
<tr class="bg-gray-200">
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
