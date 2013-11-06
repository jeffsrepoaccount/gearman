<?php
/**
 * Example call to the NumWorkers task in a blocking manner
 *
 * @author      Jeff Lambert
 * @category    Gearman
 * @package     Jnet
 * @subpackage  Tasks
 * @link        <https://github.com/jeffsrepoaccount>
 */
$request = array(
    'serviceCall' => 'numWorkers',
);

$client = new GearmanClient( );
$client->addServer( '127.0.0.1', 4730 );
print $client->doNormal( 'serviceRequest', json_encode( $request ) ) . "\n";

