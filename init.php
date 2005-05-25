<?php
/**
 * $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://www.webmasterpro.de/~ddanier/serverstats/
 * License: GPL v2 or later (http://www.gnu.org/copyleft/gpl.html)
 *
 * Copyright (C) 2005 David Danier
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// We want to write clean code ;-)
error_reporting(E_ALL);

// Define PATH, used to name the files
define('PATH', dirname(realpath(__FILE__)) . DIRECTORY_SEPARATOR);
define('INCLUDEPATH', PATH . 'includes' . DIRECTORY_SEPARATOR);
define('GRAPHPATH', PATH . 'graph' . DIRECTORY_SEPARATOR);
define('SOURCEPATH', PATH . 'sources' . DIRECTORY_SEPARATOR);
define('CACHEPATH', PATH . 'cache' . DIRECTORY_SEPARATOR);
define('RRDPATH', PATH . 'rrd' . DIRECTORY_SEPARATOR);
define('LANGPATH', PATH . 'lang' . DIRECTORY_SEPARATOR);
define('CONFIGPATH', PATH . 'config' . DIRECTORY_SEPARATOR);

// Load the needed classes
require_once(INCLUDEPATH . 'rrd.class.php');
require_once(INCLUDEPATH . 'rrdgraph.class.php');
require_once(INCLUDEPATH . 'source.class.php');
require_once(INCLUDEPATH . 'lang.class.php');
require_once(INCLUDEPATH . 'config.class.php');

// Load all needed functions
require_once(INCLUDEPATH . 'functions.php');

// Load the config
// require_once(INCLUDEPATH . 'config.php');
$config = new config(config::loadConfig(CONFIGPATH . 'main.php'), CONFIGPATH);

?>
