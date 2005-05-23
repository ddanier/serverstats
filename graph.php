<?php
/**
 * graph.php $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://www.webmasterpro.de/~ddanier/serverstats/
 * License: GPL v2 or later (http://www.gnu.org/copyleft/gpl.html)
 */

// Load all needed classes, function and everything else
require_once('init.php');
// Validate the config and the selected graph
validateConfig(false);
if (!isset($_GET['graph']))
{
	die('$_GET["graph"] missing');
}
validateGraph($_GET['graph']);
// Set the content-type (comment this out for debugging)
header('Content-type: image/png');

// Init the needed Vars
$graphindex = $_GET['graph'];
$start = isset($_GET['start']) ? $_GET['start'] : -$config['defaultperiod'];
$end = isset($_GET['end']) ? $_GET['end'] : null;
$graph = $config['graphlist'][$graphindex];
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
