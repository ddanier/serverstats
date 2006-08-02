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

// Define the look of the graphs
$config['width'] = 500;
$config['height'] = 150;
$config['usecache'] = true;

// List of all Graphs
$config['list'] = array();
// Add graphs using the simple configuration
// (To change what graphs are generated by simpleconfig please edit simple.php)
// simpleconfig::graph($config, $rootConfig['simple']);
xmlconfig::graph($config, $rootConfig['xml']);

$config['tree'] = array(
	'title' => 'All graphs',
	'filter' => '',
	'sub' => array(
		array(
			'title' => 'localhost',
			'filter' => 'host:localhost'
		),
		array ( 
			'title' => 'important',
			'filter' => 'impact:important'
		),
		array ( 
			'title' => 'datacenter1',
			'filter' => 'none',
			'sub' => array (
				array ( 
					'title' => 'switches',
					'filter' => 'groupby:switches1'
					
				), 
				array (
					'title' => 'VPS',
					'filter' => 'groupby:vps'
				)
			)
		)
	)
);
$config['types'] = array(
//	array('title' => 'Hour', 'period' => 3600), // only useful if you have a small step
	array('title' => 'Day', 'period' => 86400),
	array('title' => 'Week', 'period' => 604800),
	array('title' => 'Month', 'period' => 2678400),
	array('title' => 'Year', 'period' => 31536000)
);
// The period uses in the graph overview (index.php)
$config['defaultperiod'] = 86400;

?>
