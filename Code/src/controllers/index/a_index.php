<?php

list( $display[ 'page_error' ], $errors ) = post_errors();

$display[ 'page_title' ] = 'Home';
render(CONFIG::get('ABSPATH') . '/src/views/templates/index/index.php', CONFIG::get('ABSPATH') . '/src/views/layouts/flat.php');
