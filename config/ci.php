<?php

$config = require __DIR__.'/dev.php';

$config['aws']['queue_name'] = 'annotations--ci';

return $config;
