<?php

require_once 'models/config.php';

try
{
    CONFIG::$ENV = 'development';
    CONFIG::$APP = 'default';
    CONFIG::load();
}
catch (Exception $e)
{
    die($e->getMessage());
}

require_once 'settings.php';
