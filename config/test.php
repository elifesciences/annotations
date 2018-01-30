<?php

$config = require __DIR__.'/ci.php';

$config['api_problem']['factory']['include_exception_details'] = false;
$config['aws']['stub'] = true;
$config['api_url'] = 'https://api.elifesciences.org/';
$config['mock'] = true;

return $config;
