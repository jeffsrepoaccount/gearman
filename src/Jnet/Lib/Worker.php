<?php namespace Jnet\Lib;
/**
 * Parent class for all Tasks.
 *
 * @author      Jeff Lambert
 * @category    Gearman
 * @package     Jnet
 * @subpackage  Lib
 * @link        <https://github.com/jeffsrepoaccount>
 */

use RuntimeException;
use UnexpectedValueException;
use GearmanJob;

abstract class Worker implements iGearmanTask
{
    protected $_options     = array( );
    protected $_app         = null;
    protected $_job         = null;

    //{{{ __construct
    /**
     * Base Worker Construction
     */
    public function __construct( $options, GearmanJob $job, Application $app )
    {
        $this->_options = $options;
        $this->_job = $job;
        $this->_app = $app;
    }
    //}}}
    //{{{ __destruct
    /**
     *
     */
    public function __destruct( )
    {

    }
    //}}}
    //{{{ _validateOptions
    /**
     * Validates the data supplied to the worker. Data can be specified as 
     * 'required', in which case if the data is not set, an exception will 
     * be raised.  An instance of Jnet\Lib\Validator can also be supplied 
     * for each data point.  If the validator does not validate the supplied 
     * value, an exception will also be raised.
     *
     * @param array $validatorSettings 
     * @param array $options
     * @throws RuntimeException, UnexpectedValueExpcetion
     */
    protected function _validateOptions( $validaterSettings = array( ), array $options )
    {
        foreach( $validaterSettings as $field => $validate ) {
            if( $validate['required'] && !isset( $options[$field] ) ) {
                throw new RuntimeException( $field . ' is required' );
            }

            if( $validate['validator'] && $validate['validator'] instanceof Validator ) {
                if( !$validate['validator'] ->isValid ( ) ) {
                    throw new UnexpectedValueException( $field . ' is not valid ' );
                }
            }
        }
    }
    //}}}
}