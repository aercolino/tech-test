<!DOCTYPE html>
<html lang="en">
<?php
require_once CONFIG::get('ABSPATH') . '/src/views/partials/helpers.php';
global $include_css, $include_js, $errors;
?>

<head>
    <title> <?php xe( $display['page_title'] ); ?> </title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?= web_add_css($include_css); ?>

<?= web_add_js($include_js); ?>
</head>

<body>

    <?= $display['page_content'] ?>
    
</body>
</html>
