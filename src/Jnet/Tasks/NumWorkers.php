<?php namespace Jnet\Tasks;
/**
 * This worker returns the number of workers currently running on the same server.
 *
 * @author      Jeff Lambert
 * @category    Gearman
 * @package     Jnet
 * @subpackage  Tasks
 * @link        <https://github.com/jeffsrepoaccount>
 */
use Jnet\Lib\Worker as Worker;

class NumWorkers extends Worker
{
    /**
     * (non PHP-doc)
     * @see Jnet\Lib\iGearmanTask::doWork
     */
    public function doWork( )
    {
        exec( 'gearadmin --workers | grep ' . SERVICE_CALL, $output );
        return count( $output );
    }
}