# NodeJS Control [![build status](https://ci.gitlab.com/projects/4927/status.png?ref=master)](https://ci.gitlab.com/projects/4927?ref=master)
-----

Small class to control your node.js scripts without console access.


## Features

- set a variety of options
  * path to node binary
  * path to script
  * path to logfile
  * path to PID file (for background processes)
- execute a script in foreground
- start and stop a script in background
- view status with running pid for background processes

## Requirements
- PHP >= 5.5
- shell access for the user running you webserver

## Bugs
- on windows machines no logfiles possible
- report any other bug here: [BitBucket](https://bitbucket.org/BlackyPanther/nodejs-control/issues)

-----

### LICENSE
My scripts are published under [MIT License](https://am-wd.de/?p=about#license).
