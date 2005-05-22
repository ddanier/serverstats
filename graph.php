<?php

require_once('init.php');
header('Content-type: image/png');

$usecache = (isset($_GET['usecache']) && $_GET['usecache']) ? true : false;
$graphindex = $_GET['graph'];
$period = $_GET['period'];
$graph = $config['graphlist'][$graphindex];
$title = $graph['title'];
if (isset($_GET['title']))
{
	$title = $title . ' - ' . $_GET['title'];
}
$filename = md5($graphindex . $period . $title);
$graphfile = GRAPHPATH . $filename . '.png';

$rrdgraph = new rrdgraph($config['rrdtool'], $period, $title);

foreach($graph['content'] as $c)
{
	$intname = '';
	$rrdfile = '';
	if (in_array($c['type'], array('line', 'area', 'stack')))
	{
		$intname = $c['source'] . '_' . $c['ds'];
		$rrdfile = RRDPATH . $c['source'] . '.rrd';
		$rrdgraph->addDEF($intname, $c['ds'], $rrdfile, $c['cf']);
	}
	switch ($c['type'])
	{
		case 'line':
			$rrdgraph->addLINE($intname, $c['legend'], $c['color']);
			break;
		case 'area':
			$rrdgraph->addAREA($intname, $c['legend'], $c['color']);
			break;
		case 'stack':
			$rrdgraph->addSTACK($intname, $c['legend'], $c['color']);
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
