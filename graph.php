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

// Load all needed classes, function and everything else
require_once('init.php');
if (!isset($_GET['graph']))
{
	die('$_GET["graph"] missing');
}
// Set the content-type (comment this out for debugging)
header('Content-type: image/png');

// Init the needed Vars
$graphindex = $_GET['graph'];
$start = isset($_GET['start']) ? $_GET['start'] : -$config['graph']['defaultperiod'];
$end = isset($_GET['end']) ? $_GET['end'] : null;
$graph = $config['graph']['list'][$graphindex];
$title = $graph['title'];
if (isset($_GET['title']))
{
	$title = $title . ' - ' . $_GET['title'];
}
$usecache = $config['graph']['usecache'];

// Cachefilename is generated from all vars that may change
$filename = md5($graphindex . '_' . $start . '_' . $end . '_' . $title);
$graphfile = GRAPHPATH . $filename . '.png';

// Create Graph
$rrdgraph = new rrdgraph($config['rrdtool'], $start, $end);
$rrdgraph->setTitle($title);
$rrdgraph->setWidth($config['graph']['width']);
$rrdgraph->setHeight($config['graph']['height']);

$rrdgraph->setBase($config['graph']['width']);
$rrdgraph->setUpperLimit($config['graph']['height']);
$rrdgraph->setLowerLimit($config['graph']['width']);
$rrdgraph->setVerticalLabel($config['graph']['height']);
$rrdgraph->setUnitsExponent($config['graph']['width']);
$rrdgraph->setAltYMrtg($config['graph']['height']);
$rrdgraph->setAltAutoscale($config['graph']['width']);
$rrdgraph->setAltAutoscaleMax($config['graph']['height']);

foreach($graph['content'] as $c)
{
	$intname = '';
	$rrdfile = '';
	// If the Graphcontent need is generated from a RRD-file we need
	// to add a DEF here
	if (in_array($c['type'], array('line', 'area', 'stack', 'gprint')))
	{
		if (isset($c['source']))
		{
			$intname = $c['source'] . '_' . $c['ds'];
			$rrdfile = RRDPATH . $c['source'] . '.rrd';
			$rrdgraph->addDEF($intname, $c['ds'], $rrdfile, $c['cf']);
		}
		elseif (isset($c['name']))
		{
			$intname = $c['name'];
		}
		else
		{
			throw new Exception('You need to set either "source" or "name"');
		}
	}
	// Add the content
	switch ($c['type'])
	{
		case 'def':
			$rrdfile = RRDPATH . $c['source'] . '.rrd';
			$rrdgraph->addDEF($c['name'], $c['ds'], $rrdfile, $c['cf']);
			break;
		case 'cdef':
			$rrdgraph->addCDEF($c['name'], $c['expression']);
			break;
		case 'line':
			$rrdgraph->addLINE($intname, $c['legend'], $c['color'], $c['width']);
			break;
		case 'area':
			$rrdgraph->addAREA($intname, $c['legend'], $c['color']);
			break;
		case 'stack':
			$rrdgraph->addSTACK($intname, $c['legend'], $c['color']);
			break;
		case 'gprint':
			$rrdgraph->addGPRINT($intname, $c['format'], $c['cf']);
			break;
		case 'hrule':
			$rrdgraph->addHRULE($c['value'], $c['legend'], $c['color']);
			break;
		case 'vrule':
			$rrdgraph->addAREA($c['time'], $c['legend'], $c['color']);
			break;
		case 'comment':
			$rrdgraph->addCOMMENT($c['text']);
			break;
	}
}

if ($usecache)
{
	$rrdgraph->save($graphfile);
	readfile($graphfile);
}
else
{
	$rrdgraph->output();
}

?>
