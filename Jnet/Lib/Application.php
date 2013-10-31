<?php namespace Jnet\Lib;

class Application
{
    const MODE_BLOCKING = 0;
    const MODE_NONBLOCKING = 1;

    private $_arguments = array( );

    // Array of Job Server Objects
    private $_jobServers = array( );
    // Logger Object
    private $_logger = null;
    // Integer mode - blocking / non-blocking
    private $_mode = null;

    public function __construct( )
    {
        $this->_logger = new Logger( );
        // Default the application to blocking mode
        $this->_mode = self::MODE_BLOCKING;

        $this->log( 'APPLICATION STARTUP' );
    }

    //{{{ addJobServer
    /**
     * Connects this worker process to each of the job servers
     */
    public function addJobServers(  )
    {
        foreach( $this->_jobServers as $jobServer ) {
            $jobServer->connect( );
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
        $args = getopt( 'nbl:s:p:', array( 'nonblocking', 'blocking', 'logfile:', 'server:', 'port:' ) );

        // Determine application mode
        if( isset( $args['n'] ) || isset( $args['nonblocking'] ) ) {
            $this->_mode = self::MODE_NONBLOCKING;
        }

        // Determine location of the logfile
        if( isset( $args['l'] ) || isset( $args['logfile'] ) ) {
            
            $logfile = isset( $args['l'] ) ? 
                $args['l'] : 
                $args['logfile']
            ;

            $this->setLogFile( $logfile );
        }

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
    //{{{ beginWork
    /**
     * 
     */
    public function beginWork( )
    {
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
}