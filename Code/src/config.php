<?php

require_once 'models/config.php';

try
{
    $APPENV = getenv('APPENV');
    CONFIG::$ENV = $APPENV != '' ? $APPENV : 'development';
    CONFIG::$APP = 'default';
    CONFIG::load();
}
catch (Exception $e)
{
    die($e->getMessage());
}

require_once 'settings.php';
