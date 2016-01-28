<?php

// die (phpinfo());

error_reporting( E_ALL );

require_once( '../../config.php' );

global $url_string, $controller, $action;
try
{
    list($controller, $action, $type) = controller_action($url_string);
}
catch (Exception $e)
{

    redirect_to('no-access.php');
}

die(d_($type));
check_login($type);
unset($type);

// include CONFIG::get('ABSPATH') . 'app/c/init.php';
forward_to($controller, $action);
