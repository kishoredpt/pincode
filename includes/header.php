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

<?php if(isset($articleSchema)): ?>
<script type="application/ld+json"><?= json_encode($articleSchema,JSON_UNESCAPED_SLASHES) ?></script>
<?php endif; ?>

<?php if(isset($postalSchema)): ?>
<script type="application/ld+json"><?= json_encode($postalSchema,JSON_UNESCAPED_SLASHES) ?></script>
<?php endif; ?>

<link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header>
  <div class="container" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
    <div class="logo"><a href="/">India Pincode Locator</a></div>
    <nav>
      <a href="/">Home</a>
      <a href="/about.php">About</a>
      <a href="/contact.php">Contact</a>
      <a href="/privacy-policy.php">Privacy</a>
      <a href="/terms.php">Terms</a>
      <a href="/disclaimer.php">Disclaimer</a>
      <a href="/editorial-policy.php">Editorial</a>
    </nav>
  </div>
</header>
