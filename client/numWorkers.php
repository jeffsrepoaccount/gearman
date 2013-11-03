<?php

$request = array(
    'serviceCall' => 'numWorkers',
);

$client = new GearmanClient( );
$client->addServer( '127.0.0.1', 4730 );
print $client->doNormal( 'serviceRequest', json_encode( $request ) ) . "\n";