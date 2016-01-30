<?php

error_reporting( E_ALL );

require_once( dirname(__FILE__, 3) . '/src/config.php' );

global $url_string, $controller, $action;
try
{
    // make sure url string is canonical, including explicit index.php dy default
    if (strpos($url_string, '/index.php') !== 0) {
        $url_string = '/index.php' . $url_string;
    }
    list($controller, $action, $type) = controller_action($url_string);
    // make sure url string is canonical, including explicit index controller and action
    $url_string = merge_params($url_string, ['controller' => $controller, 'action' => $action]);
}
catch (Exception $e)
{
    die(d_($e));
    redirect_to('no-access.html');
}

// d_([CONFIG::$ENV, $controller, $action, $type]);
// check_login($type);
unset($type);

// include CONFIG::get('ABSPATH') . 'app/c/init.php';
forward_to($controller, $action);
