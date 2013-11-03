<?php
/**
 * @author      Jeff Lambert
 * @category    Gearman
 * @link        <https://github.com/jeffsrepoaccount>
 *
 * This is the entry script that creates a daemon process and attempts to connect to specified 
 * Gearman Job Server(s).  Once connected, the process will stay open, listening for requests 
 * coming in from the Gearman Job Server and attempt to dispatch them to an appropriate task 
 * handler.
 */

declare(ticks = 1);

$app = require_once __DIR__ . '/Jnet/Lib/bootstrap.php';

$arguments = isset( $argv ) ? $argv : array( );

try {

    // First, tell the application about any command line arguments
    $app->setArguments( $arguments )
    // Next, add all of the job servers to the application
        ->addJobServers( )
    // Finally, begin working
        ->beginWork( )
    ;

} catch( Exception $e ) {

    $app->logError( $e->getMessage( ) );

}

