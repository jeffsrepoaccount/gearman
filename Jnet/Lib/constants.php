<?php
/**
 * @author      Jeff Lambert
 * @category    Gearman
 * @package     Jnet
 * @subpackage  Lib
 * @link        <https://github.com/jeffsrepoaccount>
 */
define( 'DEFAULT_JOB_SERVER_IP',    '127.0.0.1'         );
define( 'DEFAULT_JOB_SERVER_PORT',  4730                );

// This is the only service call from the Gearman Job Server this worker 
// process will respond to.
define( 'SERVICE_CALL',             'serviceRequest'    );