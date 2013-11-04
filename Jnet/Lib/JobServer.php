<?php namespace Jnet\Lib;
/**
 * Job Server class. Provides a static factory method to retrieve an array 
 * of JobServer instances given an array of IPs and an array of ports.
 *
 * @author      Jeff Lambert
 * @category    Gearman
 * @package     Jnet
 * @subpackage  Lib
 * @link        <https://github.com/jeffsrepoaccount>
 */
use \GearmanWorker as GearmanWorker;

class JobServer
{
    private $_ip;
    private $_port;
    private $_app;
    
    public function __construct( Application $app, $ip = null, $port = null )
    {
        if( !$ip ) {
            $ip = DEFAULT_JOB_SERVER_IP;
        }

        if( !$port ) {
            $port = DEFAULT_JOB_SERVER_PORT;
        }

        $this->_ip = $ip;
        $this->_port = $port;
        $this->_app = $app;
    }

    //{{{ connect
    /**
     * Connects to the Gearman Job Server
     */
    public function connect( GearmanWorker $worker )
    {
        $worker->addServer( $this->_ip, $this->_port );
        return $this;
    }
    //}}}
    //{{{ create
    /**
     * Factory method that will return a number of JobServer 
     * objects as an array depending on the number of server IPs specified. 
     * The number of servers / ports do not need to match, however if the 
     * number of servers is greater than the number of ports then the 
     * additional servers will inherit the last port number specified. At 
     * least one port number must be specified.
     *
     * @param array $serverIps
     * @param array $ports
     * @return array Returns an array of Job Server Objects.
     */
    public static function create( array $serverIps, array $ports, Application $app )
    {
        if( count( $serverIps ) == 0 || count( $ports ) == 0 ) {
            throw new \InvalidArgumentException( 'Job Server Creation: At least one server IP and one port number must be supplied' );
        }

        $return = array( );
        foreach( $serverIps as $i => $serverIp ) {
            if( isset( $ports[$i] ) ) {
                $port = $ports[$i];
            }

            $app->log( 'Adding Job Server: ' . $serverIp . ':' . $port );
            $return[] = new JobServer( $app, $serverIp, $port );
        } 

        return $return;
    }
    //}}}

}