<?php

$baseDir = dirname(dirname(__FILE__));

return [
    'RootURL' => '/templates/',
    'RootPath' => implode(DIRECTORY_SEPARATOR, [$baseDir,'public','templates']),
    'CachePath' => implode(DIRECTORY_SEPARATOR, [$baseDir,'storage','sim','cache']),
    'Debug' => false
];