Gearman Application Framework
=============================
##### Author: Jeff Lambert, Version 1.1

This software provides an extensible and robust application framework for creating tasks designed to interact with the Gearman Job Server.

Command Line Arguments

    -l | --logfile      Log file to use.  Specify 'stdout' or 'stderr' to log messages to terminal.
    -s | --server       Job Server IP to connect to.  Multiple values can be specified.
    -p | --port         Job Server Port to connect to. Multiple values can be specified.

Many different server / port combinations can be used.  If none are supplied, then the default values of 127.0.0.1:4730 will be used.  If an IP is specified but no port, the port will default to 4730.  The number of ports / servers do not need to match; in the case that they do not, the last one specified will be inherited by the remaining job servers.

For example, this will connect to two job servers, 127.1.1.1:4731 and 127.1.1.2:4731

    $ php worker.php -s 127.1.1.1 -s 127.1.1.2 -p 4731

This will connect to three job servers, 127.1.1.1:4730, 127.1.1.2:4731 and 127.1.1.3:4731

    $ php worker.php -s 127.1.1.1 -s 127.1.1.2 -s 127.1.1.3 -p 4730 -p 4731

This will connect to a single job server, 127.0.0.1:4730 and log everything to stdout

    $ php worker.php -l stdout

The following will connect to a single job server, 127.0.0.1 and log everything to Logs/Worker.log

	$ php worker.php

Background Tasks
----------------

The Gearman worker tasks make no distinction between blocking and non-blocking tasks.  It is up to the client whether or not to request a task to be run as either a blocking / non-blocking task through use of either the `doNormal` or `doBackground` calls on the GearmanClient object.  However, if you wish to receive status update information about a task running in the background, then you must supply this information from within your task by using the `GearmanJob::sendStatus` method.  For an example, look at Jnet\Lib\Tasks\TestBackground to see how status information can be relayed back to the client.


Changelog
---------

*0.0.1* -   Refactorization to include moving all classes into namespaces. Removed the requirement for separate packages to define their own factory or worker parent; worker tasks can be added to different packages solely on the basis of organization, rather than for the sole purpose of creating different worker types.  Factories and subclassed workers can still be added to packages to support different 'types' of workers, but it is no longer necessary.
    
    Other Changes:

        Bugfixes, Unit Testing, 

        Added functionality for allowing separate packages to define their own factory for creating workers; this should only be necessary if the construction of worker tasks in a given package requires different logic and/or shared data than what Jnet\Lib\Worker provides.

        Support for separating tasks into different packages added