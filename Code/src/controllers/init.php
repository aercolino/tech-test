<?php

$prefix = CONFIG::get('ABSPATH') . '/src/controllers';

$controller_init = "$prefix/$controller/init.php";
if (file_exists( $controller_init )) {
    include $controller_init;
}

$action_code = "$prefix/$controller/a_$action.php";
if (file_exists( $action_code )) {
    include $action_code;
}
