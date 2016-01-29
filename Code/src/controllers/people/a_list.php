<?php

require_once CONFIG::get('ABSPATH') . '/src/models/person.php';
require_once CONFIG::get('ABSPATH') . '/src/models/storage.php';

$display['page_title'] = 'People';

list( $display['page_error'], $errors ) = post_errors();
if ($display['page_error']) {
    d_($display['page_error']);
}
if ($errors) {
    d_($errors);
}

$stored_people = Storage::read() ?? [];
if (count($stored_people)) {
    $display['stored_people'] = array_map(function ($item) { return htmlspecialchars("$item"); }, $stored_people);
    $display['people'] = array_map(function ($item) { return $item->toHash(); }, $stored_people);
}
else {
    $display['stored_people'] = [];
    $display['people'][] = array(
        'firstname' => 'Jeff',
        'surname' => 'Stelling',
    );
    $display['people'][] = array(
        'firstname' => 'Chris',
        'surname' => 'Kamara',
    );
    $display['people'][] = array(
        'firstname' => 'Alex',
        'surname' => 'Hammond',
    );
    $display['people'][] = array(
        'firstname' => 'Jim',
        'surname' => 'White',
    );
    $display['people'][] = array(
        'firstname' => 'Natalie',
        'surname' => 'Sawyer',
    );
}

$display['button_OK'] = merge_params( $url_string, array( 'action' => 'save' ) );


render( CONFIG::get('ABSPATH').'/src/views/templates/people/list.php', CONFIG::get('ABSPATH').'/src/views/layouts/flat.php' );

