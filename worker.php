<?php
/**
 *
 *
 * Command Line Arguments
 *  -n | --nonblocking  Specify that this worker process should operate in non-blocking mode.
 *  -b | --blocking     Specify that this worker process should operate in blocking mode.
 *  -l | --logfile      Log file to use.  Specify 'stdout' or 'stderr' to log messages to terminal.
 *  -s | --server       Job Server IP to connect to.  Multiple values can be specified.
 *  -p | --port         Job Server Port to connect to. Multiple values can be specified.
 *
 * Many different server / port combinations can be used.  If none are supplied, then the default 
 * values of 127.0.0.1:4730 will be used.  If an IP is specified but no port, the port will default 
 * to 4730.  The number of ports / servers do not need to match; in the case that they do not, the 
 * last one specified will be inherited by the remaining job servers.
 *
 * For example, this will connect to two job servers, 127.1.1.1:4731 and 127.1.1.2:4731
 *  $ php worker -s 127.1.1.1 -s 127.1.1.2 -p 4731
 *
 * This will connect to three job servers, 127.1.1.1:4730, 127.1.1.2:4731 and 127.1.1.3:4731
 *  $ php worker -s 127.1.1.1 -s 127.1.1.2 -s 127.1.1.3 -p 4730 -p 4731
 *
 * This will connect to a single job server, 127.1.1.1:4730 and log all errors to stdout
 *  $ php worker -l stdout
 *
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

