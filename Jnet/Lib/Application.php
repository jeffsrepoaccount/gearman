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
    /**
     * @var array _arguments
     *
     * Array of arguments provided to the application. 
     */
    private $_arguments = array( );
    /**
     * @var array
     * Array of Jnet\Lib\JobServer Objects
     */
    private $_jobServers = array( );
    /**
     * @var Jnet\Lib\Logger _logger
     * Logger Object
     */
    private $_logger = null;
    /**
     * @var GearmanWorker _worker 
     * The GearmanClient worker object this process will use
     */
    private $_worker = null;
    /**
     * @var bool _terminate
     * Flag for whether the application has received a termination signal.
     */
    private $_terminate = false;


// Public Methods

    //{{{ __construct
    /**
     * Sets up application logging, initializes GearmanWorker and registers 
     * signal handling functions
     *
     * @param array $args Command line arguments passed in
     */
    public function __construct( array $args )
    {
        // Set Instance of Logger
        $this->_logger = new Logger( );

        // Set instance of GearmanWorker
        $this->_worker = new GearmanWorker( );

        // Set Configuration Manager
        $this->_config = new ConfigurationManager( gethostname( ), $args, $this->_logger );

        // Register signal listeners
        $this->_registerSignals( );

        $this->log( 'APPLICATION STARTUP' );
    }
    //}}}
    //{{{ loadEnvironment
    /**
     * Loads configuration values from the main configuration file as well as 
     * the current environment
     */
    public function loadEnvironment( )
    {

        $this->_config
            ->loadEnvironment( )
            ->addArguments( )
        ;

        // Set the log file
        $logSettings = $this->_config->settings( 'logs' );
        if( $logSettings['file'] == 'stdout' || $logSettings['file'] == 'stderr' ) {
            $logSettings['file'] = 'php://' . $logSettings['file'];
        }
        $this->_logger->setLogFile( $logSettings['file'] );

        return $this;
    }

    //}}}
    //{{{ connectJobServers
    /**
     * Connects this worker process to each of the job servers
     *
     * @return Jnet\Lib\Application
     */
    public function connectJobServers(  )
    {
        $this->log( 'Connecting to job servers...' );

        die(__METHOD__);

        foreach( $this->_jobServers as $jobServer ) {
            $jobServer->connect( $this->_worker );
        }
        return $this;
    }
    //}}}
    //{{{ beginWork
    /**
     * This function will not return until the work loop is broken.
     *
     * @return Jnet\Lib\Application
     */
    public function beginWork( )
    {
        die( __METHOD__ );
        $this->_attachJob( )->_workLoop( );
        return $this;
    }
    //}}}
    

// End Public Methods




   
    //{{{ _loadJobServers
    /**
     * 
     */
    protected function _loadJobServers( )
    {
    // Determine job server IP addresses / ports
        if( isset( $args['s'] ) || isset( $args['server'] ) ) {
            $servers = isset( $args['s'] ) ? $args['s'] : $args['server'];
            if( is_array( $servers ) ) {
                foreach( $servers as $server ) {
                    $config['servers'][] = $server;
                }
            } else {
                $config['servers'][] = $servers;
            }
        } else {
            $servers = array( DEFAULT_JOB_SERVER_IP );
        }

        if( isset( $args['p'] ) || isset( $args['port'] ) ) {
            $ports = isset( $args['p'] ) ? $args['p'] : $args['port'];
            if( is_array( $ports ) ) {
                foreach( $ports as $port ) {
                    $config['ports'][] = $port;
                }
            } else {
                $config['ports'][] = $ports;
            }
        } else {
            $ports = array( DEFAULT_JOB_SERVER_PORT );
        }

        // Populate array of job servers
        $this->_jobServers = JobServer::create( $config['servers'], $config['ports'], $this );
    }
    //}}}
    //{{{ _registerSignals
    /**
     * Registers listener for termination and sets terminate flag when the 
     * signal is received.
     *
     * @return Jnet\Lib\Application
     */
    protected function _registerSignals( )
    {
        $terminate = &$this->_terminate;
        pcntl_signal( SIGTERM, function( ) use( $terminate )  {
            $terminate = true;
        } );

        return $this;
    }
    //}}}
    //{{{ _workLoop
    /**
     * Sets up the worker loop 
     */
    private function _workLoop( )
    {
        $this->log( 'Entering Work Loop' );

        // The Worker Loop
        while( !$this->_terminate && $this->_worker->work( ) ) {
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
     *
     * The lambda function defined here will be called every time a service 
     * request is sent to this Gearman Worker, whether running in blocking 
     * mode or non-blocking mode.
     *
     * @return Jnet\Lib\Application
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
            // The default package if no package is specified
            'package' => 'Jnet',
        );

        // Add callback function to the worker
        $this->_worker->addFunction( SERVICE_CALL, function( $job ) use( $host, $options ) {
            try {
                $request = json_decode( $job->workload( ), true );

                if( !isset( $request['serviceCall'] ) ) {
                    throw new \UnexpectedValueException( 'Service Call Not Specified' );
                }

                if( !isset( $request['options'] ) ) {
                    $request['options'] = $options;
                } else {
                    // Merge supplied options with the default options from above
                    $request['options'] = array_replace_recursive(
                        $options, $request['options']
                    );
                }

                $request['job'] = $job;

                $package = $request['options']['package'];

                $host->log( 
                    '+ Job Received: ' . $request['serviceCall'] . 
                    ' (Package: ' . $package . ')' 
                );

                // Retrieve the worker from a worker factory
                $worker = WorkerFactory::getFactory( $request, $host )->getWorker( );
                $host->log( '+ Worker Retrieved: ' . get_class( $worker ) );

                if( $worker ) {
                    $result = $worker->doWork( );
                }

            } catch( Exception $e ) {
                $host->logError( 'Worker Exception: (' . get_class( $e ) . ') ' . $e->getMessage( ) );
                $result = array( 'success' => false, 'message' => 'Worker Exception: ' . $e->getMessage( ) );
            }

            // Job Server can only relay back scalar data. If the return value 
            // is not scalar, return the JSON representation as a string.
            if( !is_scalar( $result ) ) {
                $result = json_encode( $result );
            }

            return $result;
        } );

        // Gearman Job attached to the worker
        return $this;
    }
}