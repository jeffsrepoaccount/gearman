<?php namespace Jnet\Lib;
/**
 * @author      Jeff Lambert
 * @category    Gearman
 * @package     Jnet
 * @subpackage  Lib
 * @link        <https://github.com/jeffsrepoaccount>
 */
interface iGearmanTask
{
    /**
     * All Tasks must provide an entry point for which the task is 
     * to be performed.
     */
    public function doWork( );
}