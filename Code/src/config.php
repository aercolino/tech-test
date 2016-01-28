<?php

require_once 'models/config.model.php';

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
