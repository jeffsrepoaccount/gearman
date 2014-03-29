<?php
/**
 *
 * This is the entry script that creates a daemon process and attempts to connect to specified 
 * Gearman Job Server(s).  Once connected, the process will stay open, listening for requests 
 * coming in from the Gearman Job Server and attempt to dispatch them to an appropriate task 
 * handler.
 *
 * @author      Jeff Lambert
 * @category    Gearman
 * @link        <https://github.com/jeffsrepoaccount>
 */

declare( ticks = 1 );

$app = require_once __DIR__ . '/bootstrap/bootstrap.php';

try {

    $app->loadEnvironment( )
        ->connectJobServers( )
        ->beginWork( )
    ;

} catch( Exception $e ) {

    $app->logError( $e->getMessage( ) );

}

