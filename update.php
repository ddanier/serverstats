#!/usr/bin/php
<?php

// Load all needed classes, function and everything else
require_once('init.php');

var_dump($_SERVER);
exit;

foreach ($config['sources'] as $sourcename => $source)
{
	echo "Working on $sourcename\n";
	// All needed Vars
	$cachefile = CACHEPATH . $sourcename . '.sav';
	$rrdcachefile = CACHEPATH . $sourcename . '.rrd.sav';
	$rrdfile = RRDPATH . $sourcename . '.rrd';
	// The classes may throw exceptions, so we need to catch them
	try
	{
		$source->init();
		if ($source->useCache())
		{
			if (file_exists($cachefile))
			{
				$cache = unserialize(file_get_contents($cachefile));
				$source->loadCache($cache);
				unset($cache);
			}
			else
			{
				$source->initCache();
			}
		}
		$source->refreshData();
		if ($source->useCache())
		{
			$cache = serialize($source->getCache());
			file_put_contents($cachefile, $cache);
			unset($cache);
		}
		$sourcerrd = new rrd($config['rrdtool'], $rrdfile);
		if (file_exists($rrdcachefile))
		{
			$cache = unserialize(file_get_contents($rrdcachefile));
			$sourcerrd->initOptions($cache);
			unset($cache);
		}
		else
		{
			echo "\tCreating RRD-file\n";
			$source->initRRD($sourcerrd);
			foreach ($config['archives'] as $rra)
			{
				$sourcerrd->addArchive($rra['steps'], $rra['rows'], $rra['cf']);
			}
			$sourcerrd->create();
			$cache = serialize($sourcerrd->getOptions());
			file_put_contents($rrdcachefile, $cache);
			unset($cache);
		}
		echo "\tUpdating RRD-file\n";
		$source->updateRRD($sourcerrd);
		$sourcerrd->update();
	}
	catch (Exception $e)
	{
		echo "\tError\n";
		echo $e;
	}
}
?>
