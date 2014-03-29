<?php namespace Jnet\Lib;
/**
 * @author      Jeff Lambert
 * @category    Gearman
 * @package     Jnet
 * @subpackage  Lib
 * @link        <https://github.com/jeffsrepoaccount>
 */
class Logger
{
    const DEFAULT_LOGFILE = 'Logs/Worker.log';

    private $_logFile = null;

    public function __construct( $logfile = null )
    {
        if( $logfile ) {
            $this->_logFile = $logfile;
        } else {
            $this->_logFile = self::DEFAULT_LOGFILE;
        }
    }

    public function setLogFile( $logfile )
    {
        if( is_readable( $logfile ) || in_array( $logfile, array( 'php://stdout', 'php://stderr' ) ) ) {
            $this->_logFile = $logfile;
        } else {
            touch($logfile);

            if( !is_readable( $logfile ) ) {
                throw new \RuntimeException( 'Invalid Logfile: Cannot create log file' );
            }
        }
    }

    public function log( $message )
    {
        $handle = fopen( $this->_logFile, 'a' );
        fwrite( $handle, date('Y-m-d H:i:s') . ' ' . $message . "\n" );
        fclose( $handle );
    }


}