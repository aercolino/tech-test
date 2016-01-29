<?php

$display['page_title'] = 'People';

// $data = post_data();
// list( $display['page_error'], $errors ) = post_errors();

// $display['button_OK'] = merge_params( $url_string, array( 'action' => 'save' ) );


render( CONFIG::get('ABSPATH').'/src/views/templates/people/list.php', CONFIG::get('ABSPATH').'/src/views/layouts/flat.php' );

