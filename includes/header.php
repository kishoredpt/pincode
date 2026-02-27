<?php
require_once __DIR__.'/seo.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">

<?php include __DIR__.'/seo.php'; ?>

<!-- ===============================
STRUCTURED DATA
================================ -->

<script type="application/ld+json">
{
 "@context":"https://schema.org",
 "@type":"Organization",
 "name":"India Pincode Locator",
 "url":"https://pincodelocator.co.in",
 "logo":"https://pincodelocator.co.in/logo.png"
}
</script>

<script type="application/ld+json">
{
 "@context":"https://schema.org",
 "@type":"WebSite",
 "name":"India Pincode Locator",
 "url":"https://pincodelocator.co.in",
 "potentialAction":{
   "@type":"SearchAction",
   "target":"https://pincodelocator.co.in/search.php?q={search_term}",
   "query-input":"required name=search_term"
 }
}
</script>

<?php
/* ARTICLE SCHEMA */
if(isset($articleSchema)){
echo '<script type="application/ld+json">'.json_encode($articleSchema,JSON_UNESCAPED_SLASHES).'</script>';
}
?>

<?php
/* POST OFFICE SCHEMA */
if(isset($postalSchema)){
echo '<script type="application/ld+json">'.json_encode($postalSchema,JSON_UNESCAPED_SLASHES).'</script>';
}
?>

<link rel="stylesheet" href="/assets/style.css">

</head>
<body>