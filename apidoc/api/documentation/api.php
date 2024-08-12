<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


spl_autoload_register('autoloader');
function autoloader(string $name) {

    if (file_exists('../../models/'.$name.'.php')){
        require_once '../../models/'.$name.'.php';
    }
}

require($_SERVER['DOCUMENT_ROOT'].'/apidoc/vendor/autoload.php');

$openapi = \OpenApi\Generator::scan([$_SERVER['DOCUMENT_ROOT'].'/apidoc/models']);
header('Content-Type: application/json');
echo $openapi->toJSON();