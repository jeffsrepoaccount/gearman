<?php namespace Jnet\Tasks;
/**
 * Example worker task that sends status update information back to the job 
 * server.  Any task can be called as a background task, but in order to 
 * receive status updates, they must specifically be sent by the worker with 
 * the GearmanJob::sendStatus method.
 *
 * @author      Jeff Lambert
 * @category    Gearman
 * @package     Jnet
 * @subpackage  Tasks
 * @link        <https://github.com/jeffsrepoaccount>
 * 
 */
use Jnet\Lib\Worker as Worker;

class TestBackground extends Worker
{
    /**
     * (non PHP-doc)
     * @see iGearmanTask::doWork
     * @return int
     */
    public function doWork( )
    {
        $limit = $this->_options['count'];
        $this->_app->log( '+ Counting to ' . $limit . '...' );
        $current = 0;
        while( ++$current <= $limit ) {

            // Log a message so that we can tell something is happening,
            // then send a status update back to the job server.  Sleep 
            // to simulate this taking a long time.
            $this->_app->log( $current );
            $this->_job->sendStatus( $current, $limit );
            sleep( 1 );
        

        }

        return GEARMAN_SUCCESS;
    }
}