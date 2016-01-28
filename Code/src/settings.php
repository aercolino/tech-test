<?php

date_default_timezone_set( 'Europe/Madrid' );

require_once CONFIG::get('ABSPATH') . '/src/shared/i18n.php';

// require_once CONFIG::get('ABSPATH') . '/src/shared/login.php';

global $errors, $display, $url_string;
$errors = array();
$display = array();

// mixed functions
require_once CONFIG::get('ABSPATH') . '/src/shared/functions.php';

// global exception handler
//set_exception_handler( 'global_exception_handler' );

if (! (isset($url_string) && '' != $url_string))
{
    $url_string = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
}
