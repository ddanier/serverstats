<?php

// We want to write clean code ;-)
error_reporting(E_ALL);

// Define PATH, used to name the files
define('PATH', dirname(realpath(__FILE__)) . DIRECTORY_SEPARATOR);
define('INCLUDEPATH', PATH . 'includes' . DIRECTORY_SEPARATOR);
define('GRAPHPATH', PATH . 'graph' . DIRECTORY_SEPARATOR);
define('SOURCEPATH', PATH . 'sources' . DIRECTORY_SEPARATOR);
define('CACHEPATH', PATH . 'cache' . DIRECTORY_SEPARATOR);
define('RRDPATH', PATH . 'rrd' . DIRECTORY_SEPARATOR);

// Load the needed classes
require_once(INCLUDEPATH . 'rrd.class.php');
require_once(INCLUDEPATH . 'rrdgraph.class.php');
require_once(INCLUDEPATH . 'source.class.php');

// Load all needed functions
require_once(INCLUDEPATH . 'functions.php');

// Load the config
require_once(INCLUDEPATH . 'config.php');

?>
