<?php
/**
 * $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://www.webmasterpro.de/~ddanier/serverstats/
 * License: GPL v2 or later (http://www.gnu.org/copyleft/gpl.html)
 */

$config = array(
	// Language
	'language' => 'en_US',
	// Where to find the rrdtool binary
	'rrdtool' => '/usr/bin/rrdtool',
	// Define the sources, read the sourcefiles for the needed details
	'sources' => array(
		'trafficCHAIN' => new traffic('CHAIN'),
		'mysql' => new mysql('user', 'password', 'localhost'),
		'load' => new load(),
		'users' => new users()
	),
	// Define what archives will be used (-> RRA)
	// see 'man rrdcreate' for details
	'archives' => array (
		// step = 60 (can not be changed)
		'hour' => array('steps' => 1, 'rows' => 1800, 'cf' => 'AVERAGE'), // ca. 24 Stunden
		'day' => array('steps' => 5, 'rows' => 1200, 'cf' => 'AVERAGE'), // ca. 4 Tage
		'week' => array('steps' => 10, 'rows' => 1800, 'cf' => 'AVERAGE'), // ca. 2 Wochen
		'month' => array('steps' => 60, 'rows' => 1500, 'cf' => 'AVERAGE'), // ca. 2 Monate
		'year' => array('steps' => 180, 'rows' => 12000, 'cf' => 'AVERAGE') // ca. 4 Jahre
	),
	// Define what Graphes we want in the detail view (detail.php)
	'graphtypes' => array(
		array('title' => 'Hour', 'period' => 3600),
		array('title' => 'Day', 'period' => 86400),
		array('title' => 'Week', 'period' => 604800),
		array('title' => 'Month', 'period' => 2678400),
		array('title' => 'Year', 'period' => 31536000)
	),
	// The period uses in the graph overview (index.php)
	'defaultperiod' => 86400,
	// Define the look of the graphs, mostly unused
	'graph' => array(
		'height' => 150,
		'width' => 500,
		'usecache' => true 
	),
	// Define the graphs
	'graphlist' => array(
		array(
			'title' => 'Sample',
			// Here you see all supported contenttypes
			// Every type needs _exactly_ the options
			// shown here
			'content' => array(
				// Simple Options, no need to care about the DEFs
				array(
					'type' => 'line',
					'width' => 2, // may be 1, 2 or 3
					'source' => 'sample',
					'ds' => 's1',
					'cf' => 'AVERAGE',
					'legend' => 'Sample1\n',
					'color' => 'FF0000'
				),
				array(
					'type' => 'area',
					'source' => 'sample',
					'ds' => 's2',
					'cf' => 'AVERAGE',
					'legend' => 'Sample2',
					'color' => 'FF0000'
				),
				array(
					'type' => 'stack',
					'source' => 'sample',
					'ds' => 's3',
					'cf' => 'AVERAGE',
					'legend' => 'Sample3',
					'color' => 'FF0000'
				),
				array(
					'type' => 'hrule',
					'value' => 10,
					'legend' => 'Sample4',
					'color' => 'FF0000'
				),
				array(
					'type' => 'vrule',
					'time' => 1116659895,
					'legend' => 'Sample5',
					'color' => 'FF0000'
				),
				array(
					'type' => 'comment',
					'text' => 'Sample6'
				),
				array(
					'type' => 'gprint',
					'source' => 'sample',
					'ds' => 's3',
					'cf' => 'AVERAGE', // Must be set even if using own (C)DEFs
					                   // see below
					'format' => '%lf'
				),
				// Advanced Options
				array( // Define own DEF
					'type' => 'def',
					'name' => 'vname1',
					'source' => 'sample',
					'ds' => 's4',
					'cf' => 'AVERAGE'
				),
				array( // Define own CDEF
					'type' => 'cdef',
					'name' => 'vname2',
					'expression' => 'vname1,2,/' // See 'man rrdgraph'
					                             // vnames used here are not validated!
				),
				array( // Use own (C)DEFs
					'type' => 'line',
					'width' => 2,
					// 'source', 'ds' and 'cf' is replaced with:
					'name' => 'vname2',
					'legend' => 'Sample1',
					'color' => 'FF0000'
				),
				array( // Use own (C)DEF with GPRINT
				       // Attention: 'cf' MUST stay
					'type' => 'gprint',
					'name' => 'vname2',
					'cf' => 'AVERAGE',
					'format' => '%lf'
				)
			)
		)
	)
);

?>
