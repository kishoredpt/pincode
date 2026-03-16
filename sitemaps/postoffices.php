<?php
require_once "../config/db.php";

header("Content-Type: application/xml; charset=utf-8");

function toSlug($value){
    $value=(string)$value;
    $value=html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value=iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    if($value===false){
        $value='';
    }
    $value=strtolower(trim($value));
    $value=preg_replace('/[^a-z0-9]+/','-',$value);
    return trim((string)$value,'-');
}

$limit = 10000;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1){
    $page = 1;
}
$offset = ($page - 1) * $limit;

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php
$stmt = $conn->prepare("\nSELECT officename,pincode\nFROM post_offices\nGROUP BY officename,pincode\nORDER BY pincode,officename\nLIMIT ? OFFSET ?\n");
$stmt->bind_param("ii",$limit,$offset);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()){
$officeSlug=toSlug($row['officename']);
if($officeSlug==='' || strlen($officeSlug)<3){
    continue;
}
$url="https://pincodelocator.co.in/{$officeSlug}-post-office-{$row['pincode']}";
?>
<url>
<loc><?= htmlspecialchars($url) ?></loc>
<changefreq>monthly</changefreq>
<priority>0.8</priority>
</url>
<?php } ?>
</urlset>
