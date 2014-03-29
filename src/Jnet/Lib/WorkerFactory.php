<?php namespace Jnet\Lib;
/**
 * @author      Jeff Lambert
 * @category    Gearman
 * @package     Jnet
 * @subpackage  Lib
 * @link        <https://github.com/jeffsrepoaccount>
 */
class WorkerFactory implements iWorkerFactory
{
    protected $_request = array( );
    protected $_app = null;

    //{{{ __construct
    /**
     *
     */
    protected function __construct( $request, $app )
    {
        $this->_request = $request;
        $this->_app = $app;
    }
    //}}}
    //{{{ getFactory
    /**
     * Static Factory method for retrieving an instance of a WorkerFactory.
     * Unless a package other than Jnet is specified and a new implementation 
     * of a WorkerFactory has been defined in the requested package, this 
     * method will return an instance of this class.
     *
     * @param array $request The request sent from the Job Server
     * @param Application $app
     */
    public static function getFactory( array $request, $app )
    {
        $class = $request['options']['package'] . '\\' . 
            'Lib' . '\\WorkerFactory'
        ;   

         if( file_exists(  str_replace( '\\', DIRECTORY_SEPARATOR, $class ) . '.php' ) ) {
            return new $class( $request, $app );
        } else {
            return new WorkerFactory( $request, $app );
        }


    }
    //{{{
    /**
     * The standard implementation of how a worker task is constructed. If 
     * different parameters need to be passed to construct a task, define 
     * a separate implementation from within a new implementation of 
     * iWorkerFactory (or just subclass this class and just override this 
     * method).
     *
     * @return Worker
     */
    public function getWorker( )
    {
        $worker = $this->_request['options']['package'] . '\\' . 'Tasks\\' . ucfirst( $this->_request['serviceCall'] );
        return new $worker( $this->_request['options'], $this->_request['job'], $this->_app );
    }

}