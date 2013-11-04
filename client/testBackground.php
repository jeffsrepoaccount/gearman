<?php
/**
 * This is an example script for calling a background process that does not 
 * block.  This client script actually blocks itself later checking the 
 * status update until the background task completes to show the process of 
 * how to check the status of a running task.
 *
 * @author      Jeff Lambert
 * @category    Gearman
 * @package     Jnet
 * @subpackage  Tasks
 * @link        <https://github.com/jeffsrepoaccount>
 */
$request = array(
    'serviceCall' => 'testBackground',
);

$client = new GearmanClient( );
$client->addServer( '127.0.0.1', 4730 );

// Submit Job as a Background Task
$handle = $client->doBackground( 'serviceRequest', json_encode( $request ) );

// Simulate repeatedly checking for status of the submitted job
// until it is complete.

do {

    // $client->jobStatus( $handle) returns array(
    //  0 => bool (true - job is known),
    //  1 => bool (true - job is still running),
    //  2 => int (numerator)
    //  3 => int (denominator)
    // )
    $status = $client->jobStatus( $handle );
    var_dump($status);
    echo round( ( $status[2] / $status[3] ) * 100, 2 ) . "% Complete...\n";
    sleep( 5 );

} while( $status[0] && $status[1] && ( $status[2] < $status[3] ) );

echo "Job Complete\n";
