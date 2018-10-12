<?php

$baseDir = dirname(dirname(__FILE__));

return [
    'RootURL' => '/templates/',
    'RootPath' => implode(DIRECTORY_SEPARATOR, [$baseDir,'public','templates']),
    'Cache' => true,
    'CachePath' => implode(DIRECTORY_SEPARATOR, [$baseDir,'storage','sim','cache']),
    'Debug' => false,
    'DebugListing' => '',
    ''
];