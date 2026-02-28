<?php
include("config/db.php");

$slug=$_GET['slug']??'';

$stmt=$conn->prepare("SELECT * FROM articles WHERE slug=? LIMIT 1");
$stmt->bind_param("s",$slug);
$stmt->execute();

$res=$stmt->get_result();

if($res->num_rows==0){
header("Location:/404.php");
exit;
}

$article=$res->fetch_assoc();

/* SEO */
$pageTitle=$article['title'];
$metaDescription=substr(strip_tags($article['content']),0,155);

include("includes/header.php");
?>

<div class="container">

<h1><?= htmlspecialchars($article['title']); ?></h1>

<div class="article">
<?= $article['content']; ?>
</div>

</div>

<!-- ARTICLE STRUCTURED DATA -->
<script type="application/ld+json">
{
"@context":"https://schema.org",
"@type":"Article",
"headline":"<?= addslashes($article['title']); ?>",
"datePublished":"<?= $article['created_at']; ?>",
"author":{
 "@type":"Organization",
 "name":"India Pincode Locator"
},
"publisher":{
 "@type":"Organization",
 "name":"India Pincode Locator",
 "logo":{
   "@type":"ImageObject",
   "url":"https://pincodelocator.co.in/logo.png"
 }
}
}
</script>

<?php include("includes/footer.php"); ?>