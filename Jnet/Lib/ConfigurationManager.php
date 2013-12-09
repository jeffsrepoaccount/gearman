<?php namespace Jnet\Lib;

use Jnet\Lib\CLI\Argument;

class ConfigurationManager
{
    const MAIN_CONFIG = 'config.ini';
    const CONFIG_DIR = 'Config';

    protected static $_environments = array(
        'local', 'develop', 'staging', 'production', 'testing'
    );

    // Available arguments
    protected static $_shortOpts = 'l:s:p:';
    protected static $_longOpts = array(
        'logfile:', 'server:', 'port:'
    );

    /**
     * Current environment name
     * @var string
     */
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
     * file. If valid environment settings can be found for the given host, 
     * the environment settings are first merged together and then the entry 
     * for servers is specifically replaced to achieve both an inheritance 
     * style relationship between the two environments as well as allow an 
     * override and replace in those instances that matter.
     *
     * @return Jnet\Lib\ConfigurationManager
     */
    public function loadEnvironment( )
    {
        $this->_environment = $this->_parseIni( self::CONFIG_DIR . DS . self::MAIN_CONFIG );

        // Determine what environment the current host belongs to
        $currentEnv = $this->_environmentForHost( 
            $this->_environment, 
            self::$_environments 
        );

        if( $currentEnv ) {
            $this->_environmentName = $currentEnv;
            $hostEnvironment = $this->_parseIni( 
                self::CONFIG_DIR . DS . $currentEnv . '.ini' 
            );

            // First, deep replace anything in the current environment that is 
            // present in the host's environment
            $this->_environment = array_replace_recursive( 
                $this->_environment, $hostEnvironment
            );

            // Specifically set the Job Servers to be those listed in the host 
            // environment. If the host environment does not set any servers, 
            // the default servers will still be populated
            if( isset( $hostEnvironment['servers'] ) ) {
                $this->_environment['servers'] = $hostEnvironment['servers'];
            }
        }

        // If current host is not assigned an environment, default configuration remains.
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

        var_dump($this->_environment );
        die(__METHOD__);
        
        $arguments = Argument::get( );

        foreach( $arguments as $argument ) {
            switch( $argument->arg ) {
                case Argument::LOG_TYPE:
                    // Change the current log file
                    $this->_environment['logs']['file'] = $argument->value;
                    break;
                case Argument::SERVER_TYPE:
                    break;
                case Argument::ENV_TYPE:
                    break;
            }
        }

        


        // Append command line arguments into the environment
        $this->_environment['servers'] = array_merge(
            $this->_environment['servers'],
            $normalArgs['server']
        );

        
        return $this;
    }
    //}}}
    //{{{ options
    /**
     * Retrieve the current environment state
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
     * Retrieve the current environment state for a specific key
     *
     * @return mixed
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
     * Helper method to retrieve an applicable environment based on the 
     * current hostname.
     *
     * @param array $defaultEnvironment
     * @return string
     */
    protected function _environmentForHost( $defaultEnvironment, array $availableEnvironments )
    {
        foreach( $availableEnvironments as $env ) {

            if( isset( $defaultEnvironment['hosts'][ $env ] ) ) {

                foreach( $defaultEnvironment['hosts'][$env] as $host ) {
                    if( $host == $this->_hostname ) {
                        return $env;
                    }

                }
            }
        }

        return null;
    }
    //}}}
}