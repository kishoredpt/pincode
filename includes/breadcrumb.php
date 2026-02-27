<?php
function breadcrumb_schema($items)
{
    $position = 1;
    $data = [];

    foreach($items as $name=>$url){
        $data[]=[
            "@type"=>"ListItem",
            "position"=>$position++,
            "name"=>$name,
            "item"=>$url
        ];
    }

echo '<script type="application/ld+json">
{
"@context":"https://schema.org",
"@type":"BreadcrumbList",
"itemListElement":'.json_encode($data).'
}
</script>';
}
?>

<?php if(!empty($breadcrumbs)): ?>

<nav class="breadcrumb">
<?php
$position=1;
$schema=[];

foreach($breadcrumbs as $name=>$url){
echo "<a href='$url'>$name</a> » ";

$schema[]=[
 "@type"=>"ListItem",
 "position"=>$position++,
 "name"=>$name,
 "item"=>$url
];
}
?>
</nav>

<script type="application/ld+json">
{
 "@context":"https://schema.org",
 "@type":"BreadcrumbList",
 "itemListElement": <?=json_encode($schema,JSON_UNESCAPED_SLASHES)?>
}
</script>

<?php endif; ?>