<?php namespace Jnet\Lib;

class ConfigurationManager
{
    const MAIN_CONFIG = 'config.ini';
    const CONFIG_DIR = 'Config';

    protected static $_environments = array(
        'local', 'develop', 'staging', 'production'
    );

    // Available arguments
    protected static $_shortOpts = 'l:s:p:';
    protected static $_longOpts = array(
        'logfile:', 'server:', 'port:'
    );


    protected $_environmentName = 'default';

    protected $_environment;
    protected $_hostname;
    protected $_logger;

    protected $_loadedConfigFile;

    protected $_options = array( );
    protected $_args = array( );

    //{{{ __construct
    /**
     * Configuration manager needs the hostname and command line
     * argument overrides
     *
     * @param string $hostname
     * @param array $args
     */
    public function __construct( $hostname = '', array $args = null, Logger $logger )
    {
        $this->_hostname = $hostname;
        $this->_args = $args ? $args : array( );
        $this->_logger = $logger;

    }
    //}}}
    //{{{ loadEnvironment
    /**
     * Sets the environment initially to that set in the main configuration 
     * file. If a valid environment override can be found for the current 
     * host, the resulting settings are 
     *
     * @return Jnet\Lib\ConfigurationManager
     */
    public function loadEnvironment( )
    {
        $this->_environment = $this->_parseIni( self::CONFIG_DIR . DS . self::MAIN_CONFIG );

        // Determine what environment the current host belongs in
        $currentEnv = $this->_environmentForHost( $this->_environment, self::$_environments );

        if( $currentEnv ) {
            $this->_environmentName = $currentEnv;
            $hostEnvironment = $this->_parseIni( self::CONFIG_DIR . DS . $currentEnv . '.ini' );

            // First, do a deep replace
            $this->_environment = array_replace_recursive( 
                $this->_environment, $hostEnvironment
            );

            // Replace all servers / ports in default configuration with that 
            // of the current environment, if available.
            if( isset( $hostEnvironment['servers']['servers'] ) ) {
                $this->_environment['servers']['servers'] = $hostEnvironment['servers']['servers'];
            }

            if( isset( $hostEnvironment['ports']['ports'] ) ) {
                $this->_environment['ports']['ports'] = $hostEnvironment['ports']['ports'];
            }
        }

        return $this;
    }
    //}}}
    //{{{ addArguments
    /**
     * Arguments augment the current environment in an additive manner. Any 
     * servers / ports passed in via the command line are added to the list of 
     * servers to attempt to connect to.
     *
     * @return Jnet\Lib\ConfigurationManager
     */
    public function addArguments( )
    {
        $args = getopt( self::$_shortOpts, self::$_longOpts );

        // Normalize short opt / long opt entries
        $normalArgs = array( );
        $keys = array(
            's' => 'server', 'p' => 'port', 'l' => 'logfile'
        );
        foreach( $keys as $short => $long ) {

            if( !isset( $args[$short] ) ) {
                $args[$short] = array( );
            } else if( !is_array( $args[$short] ) ) {
                $args[$short] = array( $args[$short] );
            }

            if( !isset( $args[$long] ) ) {
                $args[$long] = array( );
            } else if( !is_array( $args[$long] ) ) {
                $args[$long] = array( $args[$long] );
            }

            $normalArgs[$long] = array_merge( $args[$short], $args[$long] );
        }

        // Append command line arguments into the environment
        $this->_environment['servers']['servers'] = array_merge(
            $this->_environment['servers']['servers'],
            $normalArgs['server']
        );

        $this->_environment['ports']['ports'] = array_merge(
            $this->_environment['ports']['ports'],
            $normalArgs['port']
        );

        var_dump( $this->_environment );

        die( __METHOD__ );

        echo 'environment';
        var_dump( $this->_environment );

        die(__METHOD__);
        
        return $this;
    }
    //}}}
    //{{{ options
    /**
     *
     *
     * @return array
     */
    public function settings( )
    {
        return $this->_environment;
    }
    //}}}
    //{{{ option
    /**
     *
     */
    public function setting( $key )
    {
        return ( isset( $this->_environment[$key] ) ? $this->_environment[$key] : null );
    }
    //}}}



    //{{{ _parseIni
    /**
     * Return the results of parsing an ini file if the file exists, an empty 
     * array otherwise. Set a flag to record the most recently read file.
     * 
     * @param string $file 
     * @return array
     */
    protected function _parseIni( $file )
    {
        if( file_exists( $file ) ) {
            $this->_loadedConfigFile = $file;
            return parse_ini_file( $file, true );
        }

        return array( );
    }
    //}}}
    //{{{ _environmentForHost
    /**
     * Helper method to retrieve an applicable environment string based on the 
     * current hostname.
     *
     * @param array $defaultEnvironment
     * @return string
     */
    protected function _environmentForHost( $defaultEnvironment, array $availableEnvironments )
    {
        foreach( $availableEnvironments as $env ) {
            $key = $env . '_hosts';
            if( isset( $defaultEnvironment[$key]['hosts'] ) ) {
                foreach( $defaultEnvironment[$key]['hosts'] as $host ) {
                    if( $host == $this->_hostname ) {  
                        return $env;
                    }
                }
            }
        }

        return '';
    }
    //}}}
}