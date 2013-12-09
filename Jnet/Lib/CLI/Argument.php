<?php namespace Jnet\Lib\CLI;
/**
 * Easiest use case:
 *  - Set entries in $_valid and $_shorts to arguments you expect as input. 
 *  - Use Argument::get( ) to retrieve an array of Argument objects
 *    for the current execution context.
 * 
 */
use \InvalidArgumentException;

class Argument
{
    // Useful constants to add for programmatic access to key entries in 
    // $_valid array
    const LOG_TYPE      = 'logfile';
    const SERVER_TYPE   = 'server';
    const ENV_TYPE      = 'environment';

    /**
     * Array of valid arguments, keyed by long version of the argument.
     * Boolean value indicates whether argument expects a value or not. False 
     * means the argument is a flag only.
     * @var array
     */
    protected static $_valid = array(
        'logfile'       => true, 
        'server'        => true, 
        'environment'   => true,
        'flag'        => false,
    );

    /**
     * Mapping of short version to long version of arguments. Each value in 
     * this array should have a corresponding key in the $_valid array.
     * @var array
     */
     protected static $_shorts = array(
        'l' => 'logfile',
        's' => 'server',
        'e' => 'environment',
        'f' => 'flag'
     );

     /**
      * Entry in $_valid array corresponding to this object's argument name
      * @var string
      */
     public $arg = '';

     /**
      * Value passed assigned to this argument (set to false if argument is 
      * a flag).
      * @var string
      */
     public $value = '';

     //{{{ __construct
     /**
      * Constructs a new instance, validates $arg
      *
      * @throws InvalidArgumentException If $arg is not valid
      */
     public function __construct( $arg, $value )
     {
        if( !$this->_isValid( $arg ) ) {
            throw new InvalidArgumentException( 'Invalid Argument: ' . $arg );    
        }
        // Normalize arguments to store long version only
        if( !isset( self::$_valid[$arg] ) ) {
            $arg = self::$_shorts[$arg];
        }

        $this->arg = $arg;
        $this->value = $value;
     }
     //}}}
     //{{{ _isValid
     /**
      * Helper method to determine if an argument is valid
      *
      * @param string $arg
      * @return boolean
      */
     protected function _isValid( $arg )
     {
        if( isset( self::$_valid[ $arg ] ) ) {
            return true;
        }
        return isset( self::$_shorts[ $arg ] );
     }
     //}}}
     //{{{ get
    /**
     * Factory function for retrieving an array of valid Argument objects based 
     * off of the input to the current script.
     *
     * @return array
     */
    public static function get( )
    {
        
        $shorts = array_keys( self::$_shorts );
        $longs = self::$_valid;

        foreach( $shorts as $k => $short ) {
            if( self::$_valid[ self::$_shorts[ $short] ] ) {
                $shorts[$k] = $short . ':';
                // Change long key
                $longs[self::$_shorts[$short] . ':' ] = $k;
                unset( $longs[ self::$_shorts[$short] ] );
            }
        }

        $shorts = implode( $shorts );
        $longs = array_keys( $longs );

        $args = getopt( $shorts, $longs );

        $return = array( );
        foreach( $args as $arg => $value ) {
            try {
                $return[] = new Argument( $arg, $value );
            } catch( InvalidArgumentException $e ) {
                echo $arg . " is not valid\n";
            }
        }

        var_dump( $return );
        die(__METHOD__);
    }
    //}}}
}