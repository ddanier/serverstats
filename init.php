<?php
/**     
 * $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://serverstats.berlios.de/
 * License: GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
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

// Load all needed functions
require_once(INCLUDEPATH . 'functions.php');

// Load the config
$config = new config(CONFIGPATH);

// TODO: move this out of init.php
if (isset($_GET['tree']) && !empty($_GET['tree']))
{
	$tree = extractTree($_GET['tree']);
	$filter = extractFilterFromTree($tree);
}
elseif (isset($_GET['filter']) && !empty($_GET['filter']))
{
	$tree = extractTree();
	$filter = extractFilter($_GET['filter']);
}
else
{
	$tree = extractTree();
	$filter = array();
}

?>
