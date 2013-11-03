<?php namespace Jnet\Lib;
/**
 * @author      Jeff Lambert
 * @category    Gearman
 * @package     Jnet
 * @subpackage  Lib
 * @link        <https://github.com/jeffsrepoaccount>
 */
use \GearmanWorker as GearmanWorker;

class Application
{
    const CONFIG_FILE = 'config.ini';

    const MODE_BLOCKING = 0;
    const MODE_NONBLOCKING = 1;

    private $_arguments = array( );

    // Array of Job Server Objects
    private $_jobServers = array( );
    // Logger Object
    private $_logger = null;
    // Integer mode - blocking / non-blocking
    private $_mode = null;
    // The GearmanClient worker object this process will use
    private $_worker = null;

    public function __construct( )
    {
        $this->_logger = new Logger( );
        // Default the application to blocking mode
        $this->_mode = self::MODE_BLOCKING;
        $this->_worker = new GearmanWorker( );

    }

    //{{{ addJobServer
    /**
     * Connects this worker process to each of the job servers
     */
    public function addJobServers(  )
    {
        $this->log( 'Connecting to job servers...' );
        foreach( $this->_jobServers as $jobServer ) {
            $jobServer->connect( $this->_worker );
        }
        return $this;
    }
    //}}}
    //{{{ setArguments
    /**
     * Sets the command line arguments. Blocking argument is added for 
     * completeness; it is not necessary to ever specify that argument since 
     * that is the default mode of the application.
     *
     * @return Jnet\Lib\Application
     */
    public function setArguments( )
    {
        // Read Config file
        $config = $this->_readConfig( );

        $args = getopt( 'nbl:s:p:', 
            array( 'nonblocking', 'blocking', 'logfile:', 'server:', 'port:' ) 
        );

        // Determine application mode. Default mode is blocking, so 
        // only switch mode if nonblocking is specified.
        if( isset( $args['n'] ) || isset( $args['nonblocking'] ) ) {
            $config['mode']['nonblocking'] = true;
        } 

        // Determine location of the logfile
        if( isset( $args['l'] ) || isset( $args['logfile'] ) ) {
            
            $logfile = isset( $args['l'] ) ? 
                $args['l'] : 
                $args['logfile']
            ;

            $this->setLogFile( $logfile );
        }

        $this->log( 'APPLICATION STARTUP' );

        // Determine job server IP addresses / ports
        if( isset( $args['s'] ) || isset( $args['server'] ) ) {
            $servers = isset( $args['s'] ) ? $args['s'] : $args['server'];
            if( !is_array( $servers ) ) {
                $servers = array( $servers );
            }
        } else {
            $servers = array( DEFAULT_JOB_SERVER_IP );
        }

        if( isset( $args['p'] ) || isset( $args['port'] ) ) {
            $ports = isset( $args['p'] ) ? $args['p'] : $args['port'];
            if( !is_array( $ports ) ) {
                $ports = array( $ports );
            }
        } else {
            $ports = array( DEFAULT_JOB_SERVER_PORT );
        }

        // Populate array of job servers
        $this->_jobServers = JobServer::create( $servers, $ports, $this );

       return $this;
    }
    //}}}
    //{{{
    /**
     * Read in configuration specified by the 
     */
    protected function _readConfig( )
    {
        if( ! ( $config = parse_ini_file( self::CONFIG_FILE ) ) ) {
            $config = array(
                'mode' => array(
                    'blocking' => true,
                    'nonblocking' => false,
                ),
                'servers' => array(
                    '127.0.0.1',
                ),
                'ports' => array(
                    4730,
                ),
            );
        }
        return $config;
    }
    //}}}
    //{{{ beginWork
    /**
     * 
     */
    public function beginWork( )
    {
        $this->_attachJob( );

        switch( $this->_mode ) {
            case self::MODE_NONBLOCKING:
                $this->_workNonBlocking( ); break;
            case self::MODE_BLOCKING:   default:
                $this->_workBlocking( ); break;
        }

        return $this;
    }
    //}}}
    //{{{ _workNonBlocking
    /**
     * Sets up the worker loop for non-blocking operation
     */
    private function _workNonBlocking( )
    {
        $this->log( 'Beginning Non-Blocking Operation' );
    }
    //}}}
    //{{{ _workBlocking
    /**
     * Sets up the worker loop for blocking operation
     */
    private function _workBlocking( )
    {
        $this->log( 'Beginning Blocking Operation' );

        while( $this->_worker->work( ) ) {
            $this->log( '+ Job Complete' );
        }

        $this->_worker->unRegisterAll( );
    }
    //}}}
    //{{{
    /**
     * Pass-thru to the logger class
     *
     * @param string $message
     * @return Jnet\Lib\Application
     */
    public function log( $message )
    {
        $this->_logger->log( $message );

        return $this;
    }
    //}}}
    //{{{ logError
    /**
     * Pass-thru to the log method.  Appends (ERROR) to the message to allow 
     * for easier log filtering.
     *
     * @param string $message
     * @return Jnet\Lib\Application
     */
    public function logError( $message ) 
    {
        return $this->log( '(ERROR) ' . $message);
    }
    //}}}
    //{{{ setLogFile
    /**
     * @param string $logFile
     * @param Jnet\Lib\Application
     */
    public function setLogFile( $logFile = null )
    {
        if( !$logFile ) {
            $logFile = 'php://stdout';
        }

        if( in_array( $logFile, array( 'stdout', 'stderr' ) ) ) {
            $logFile = 'php://' . $logFile;
        }

        $this->_logger->setLogFile( $logFile );

        return $this;
    }
    //}}}
    //{{{ _attachJob
    /**
     * The logic exists here for handling each service request, inside a lambda 
     * function for the purpose of protecting the work loop from being 
     * broken when exceptions are thrown from within worker tasks.
     */
    private function _attachJob( )
    {
        // Can't specify 'this' as a closure variable, so grab a reference 
        // to use from within the lambda
        $host = $this;
        // These options will represent the default options handed to all 
        // service calls.  Any value provided from a client request will 
        // override any values specified here.
        $options = array(
            'package' => 'Jnet',
        );
        $this->_worker->addFunction( SERVICE_CALL, function( $job ) use( $host, $options ) {
            try {
                $request = json_decode( $job->workload( ), true );

                if( !isset( $request['serviceCall'] ) ) {
                    throw new \UnexpectedValueException( 'Service Call Not Specified' );
                }

                if( !isset( $request['options'] ) ) {
                    $request['options'] = $options;
                } else {
                    $request['options'] = array_replace_recursive(
                        $options, $request['options']
                    );
                }

                $package = $request['options']['package'];

                $host->log( 
                    '+ Job Received: ' . $request['serviceCall'] . 
                    ' (Package: ' . $package . ')' 
                );

                // Retrieve the worker from a worker factory
                $worker = WorkerFactory::getFactory( $request, $host )->getWorker( );

                if( $worker ) {
                    $result = $worker->doWork( );
                }

            } catch( Exception $e ) {
                $host->logError( 'Worker Exception: ' . $e->getMessage( ) );

                $result = array( 'success' => false, 'message' => 'Worker Exception: ' . $e->getMessage( ) );
            }

            if( !is_scalar( $result ) ) {
                $result = json_encode( $result );
            }

            return $result;
        } );

        return $this;
    }
}