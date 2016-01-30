<?php

require_once CONFIG::get('ABSPATH') . '/src/models/person.php';
require_once CONFIG::get('ABSPATH') . '/src/models/storage.php';

try {

    $people = $_POST['people'];
    foreach ($people as $key => $value) {
        $people[$key] = new Person($value);  // this cleans up posted data
    }

    Storage::write($people);    
    $_SESSION['info'] = 'People saved.';              
    
    redirect_to( '/index.php/people/list' );
}
catch ( Exception $e ) {
    $_SESSION['page_error'] = $e->getMessage();
    $_SESSION['page_post'] = $_POST;
    $_SESSION['errors'] = $errors;
    redirect_to( '/index.php/people/list' );
}
