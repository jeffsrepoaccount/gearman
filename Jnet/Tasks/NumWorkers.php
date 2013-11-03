<?php namespace Jnet\Tasks;

use Jnet\Lib\Worker as Worker;

class NumWorkers extends Worker
{
    public function doWork( )
    {
        exec( 'gearadmin --workers | grep ' . SERVICE_CALL, $output );
        return count( $output );
    }
}