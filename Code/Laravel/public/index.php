<?php

error_reporting( E_ALL );

require_once( dirname(__FILE__, 3) . '/src/config.php' );

global $url_string, $controller, $action;
try
{
    list($controller, $action, $type) = controller_action($url_string);
}
catch (Exception $e)
{
    die(d_($e));
    redirect_to('no-access.html');
}

d_([CONFIG::$ENV, $controller, $action, $type]);
// check_login($type);
unset($type);

// include CONFIG::get('ABSPATH') . 'app/c/init.php';
forward_to($controller, $action);
