<?php
define( 'WORKER_START', microtime( true ) );

ini_set( 'default_socket_timeout', 300 );

// First require the autoloader
require_once __DIR__ . '/autoloader.php';
require_once __DIR__ . '/constants.php';

return new Jnet\Lib\Application;
