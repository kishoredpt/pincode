<?php
http_response_code(404);

$pageTitle="Page Not Found";
$metaDescription="The requested page could not be found.";

include 'includes/header.php';
?>

<h1>404 - Page Not Found</h1>

<p>
The page you are looking for does not exist.
</p>

<a href="/">Return to Homepage</a>

<?php include 'includes/footer.php'; ?>