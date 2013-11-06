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

// Generate random number for the background task to count to
srand( );
$count = rand( 10, 30 );

$request = array(
    'serviceCall' => 'testBackground',
    'options' => array(
        'count' => $count,
    ),
);

$client = new GearmanClient( );
$client->addServer( '127.0.0.1', 4730 );

// Submit Job as a Background Task
$handle = $client->doBackground( 'serviceRequest', json_encode( $request ) );

// Simulate repeatedly checking for status of the submitted job
// until it is complete.

// Sleep to avoid race condition where the status is requested for a job 
// that has net yet finished registering itself on the job/worker servers
sleep( 1 );

do {

    // $client->jobStatus( $handle) returns array(
    //  0 => bool (true - job is known),
    //  1 => bool (true - job is still running),
    //  2 => int (numerator)
    //  3 => int (denominator)
    // )
    $status = $client->jobStatus( $handle );
    // Don't divide by zero
    if( $status[3] > 0 ) {
        echo round( ( $status[2] / $status[3] ) * 100, 2 ) . "% Complete...\n";
        sleep( 2 );
    }

} while( $status[0] && $status[1] && ( $status[2] < $status[3] ) );

echo "Job Complete\n";
