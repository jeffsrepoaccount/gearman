Gearman Application Framework

This software provides an extendible and robust application framework for creating tasks designed
to interact with the Gearman Job Server.

Command Line Arguments

    -n | --nonblocking  Specify that this worker process should operate in non-blocking mode.
    -b | --blocking     Specify that this worker process should operate in blocking mode.
    -l | --logfile      Log file to use.  Specify 'stdout' or 'stderr' to log messages to terminal.
    -s | --server       Job Server IP to connect to.  Multiple values can be specified.
    -p | --port         Job Server Port to connect to. Multiple values can be specified.

Many different server / port combinations can be used.  If none are supplied, then the default 
values of 127.0.0.1:4730 will be used.  If an IP is specified but no port, the port will default 
to 4730.  The number of ports / servers do not need to match; in the case that they do not, the 
last one specified will be inherited by the remaining job servers.

For example, this will connect to two job servers, 127.1.1.1:4731 and 127.1.1.2:4731
    $ php worker -s 127.1.1.1 -s 127.1.1.2 -p 4731

This will connect to three job servers, 127.1.1.1:4730, 127.1.1.2:4731 and 127.1.1.3:4731
    $ php worker -s 127.1.1.1 -s 127.1.1.2 -s 127.1.1.3 -p 4730 -p 4731

This will connect to a single job server, 127.1.1.1:4730 and log everything to stdout
    $ php worker -l stdout

@author Jeff Lambert <jefflikeschicken@gmail.com>
@version 1.1
@changelog

1.2 -   Completion of Unit Tests for entire base application and all library functions.

1.1 -   Refactorization to include moving all classes into namespaces. Removed the requirement for 
        separate packages to define their own factory or worker parent; worker tasks can be 
        added to different packages solely on the basis of organization, rather than for the sole 
        purpose of creating different worker types.

1.0 -   First deployment of a working application.

0.9 -   Bugfixes, Unit Testing, 

0.8 -   Added functionality for allowing separate packages to define their own factory for creating 
        workers; this should only be necessary if the construction of worker tasks in a given package 
        requires different logic and/or shared data than what Jnet\Lib\Worker provides.

0.7 -   Support for separating tasks into different packages added