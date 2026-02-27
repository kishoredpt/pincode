function officeArticle($row){

return "
<h1>{$row['office_name']} Post Office - Pincode {$row['pincode']}</h1>

<p>{$row['office_name']} post office is located in
{$row['district']} district of {$row['state']}.</p>

<p>This post office provides {$row['delivery_status']} services.</p>
";
}