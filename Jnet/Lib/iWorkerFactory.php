<?php namespace Jnet\Lib;
/**
 * @author      Jeff Lambert
 * @category    Gearman
 * @package     Jnet
 * @subpackage  Lib
 * @link        <https://github.com/jeffsrepoaccount>
 */
interface iWorkerFactory
{
    /**
     * Each worker factory must provide an interface to 
     * retrieve a worker object.
     *
     * @return Jnet\Lib\Worker
     */
    public function getWorker( );
}