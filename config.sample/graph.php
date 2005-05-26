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

$config = array();
// Define the look of the graphs
$config['width'] = 150;
$config['height'] = 500;
$config['usecache'] = true;
// List of all Graphs
$config['list'] = array(
	array(
		'title' => 'Sample',
		// You can use some of the options for 'rrdtool graph' here:
		// base, upperLimit, lowerLimit, verticalLabel, unitsExponent, 
		// altYMrtg (bool), altAutoscale (bool), altAutoscaleMax (bool)
		// Options marked with 'bool' can only be true or false
		// for example:
		'verticalLabel' => 'SampleLabel',
		'altAutoscaleMax' => true,
		// Here you see all supported contenttypes
		// Every type needs _exactly_ the options
		// shown here
		'content' => array(
			// Simple Options, no need to care about the DEFs
			array(
				'type' => 'line',
				'width' => 2, // may be 1, 2 or 3 (optional, default: 2)
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
);
// Define what Graphes we want in the detail view (detail.php)
$config['types'] = array(
	array('title' => 'Hour', 'period' => 3600),
	array('title' => 'Day', 'period' => 86400),
	array('title' => 'Week', 'period' => 604800),
	array('title' => 'Month', 'period' => 2678400),
	array('title' => 'Year', 'period' => 31536000)
);
// The period uses in the graph overview (index.php)
$config['defaultperiod'] = 86400;

?>
